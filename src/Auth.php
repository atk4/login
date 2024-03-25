<?php

declare(strict_types=1);

namespace Atk4\Login;

use Atk4\Core\AppScopeTrait;
use Atk4\Core\ContainerTrait;
use Atk4\Core\DiContainerTrait;
use Atk4\Core\Factory;
use Atk4\Core\HookTrait;
use Atk4\Core\InitializerTrait;
use Atk4\Core\TrackableTrait;
use Atk4\Data\Exception;
use Atk4\Data\Field\PasswordField;
use Atk4\Data\Model;
use Atk4\Data\Persistence;
use Atk4\Login\Cache\Session;
use Atk4\Login\Form as LoginForm;
use Atk4\Login\Layout\Narrow;
use Atk4\Login\Model\User;
use Atk4\Ui\App;
use Atk4\Ui\Form;
use Atk4\Ui\Layout;
use Atk4\Ui\VirtualPage;

class Auth
{
    use AppScopeTrait;
    use ContainerTrait;
    use DiContainerTrait;
    use HookTrait;
    use InitializerTrait;
    use TrackableTrait;

    public const HOOK_LOGGED_IN = self::class . '@loggedIn';
    public const HOOK_BAD_LOGIN = self::class . '@badLogin';

    /**
     * Contains information about a current user. Unlike Model this will
     * contain a record loaded from session cache.
     *
     * @var User
     */
    public $user;

    /** @var string Which field to look up user by. */
    public $fieldLogin = 'email';

    /** @var string Password to be verified when authenticating. */
    public $fieldPassword = 'password';

    /**
     * Perform check automatically and display a Login form when 'setModel' takes place.
     *
     * This is a transparent way to add authentication to an existing application.
     *
     * @var bool
     */
    public $check = true;

    /** @var bool Should use some caching (in session for example) or not? */
    public $cacheEnabled = true;

    /** @var array Cache class to use. */
    public $cacheClass = [Session::class];

    /** @var array Options for cache class. */
    public $cacheOptions = [];

    /** @var Session Cache object. */
    protected $cache;

    /**
     * Login Form. If you want to use a different LoginForm you can pass
     * a seed here.
     *
     * @var array
     */
    public $formLoginSeed = [LoginForm\Login::class];

    /** @var array Seed that would create VirtualPage for adding Preference page content */
    public $preferencePage = [VirtualPage::class];

    /** @var string Which is the index page? This page should have auth / check. */
    public $pageDashboard;

    /** @var string|null Redirect here after successful login, null to move to the originating URL */
    public $pageAfterLogin;

    /** @var string User will be sent to exit page when he logs out. */
    public $pageExit = 'index';

    /** @var bool Should we add User Menu to Admin layout? */
    public $hasUserMenu = true;

    /** @var bool Should we display and handle preferences link in user menu? */
    public $hasPreferences = true;

    /**
     * @param array $options
     */
    public function __construct(App $app, $options = [])
    {
        $this->setApp($app);
        $this->setDefaults($options);

        if ($this->cacheEnabled) {
            $this->cache = Factory::factory($this->cacheClass, array_merge([1 => $this->getApp()], $this->cacheOptions));
        }
    }

    /**
     * Specify a model for a user check here.
     *
     * @param User $model
     *
     * @return $this
     */
    public function setModel(Model $model, string $fieldLogin = null, string $fieldPassword = null)
    {
        if ($this->user !== null) {
            throw new Exception('Model is already set');
        }

        $this->user = $model->createEntity();

        if ($fieldLogin !== null) {
            $this->fieldLogin = $fieldLogin;
        }

        if ($fieldPassword !== null) {
            $this->fieldPassword = $fieldPassword;
        }

        if ($this->cacheEnabled) {
            $this->loadFromCache();
        }

        // update cache after changes saved in user model
        if ($this->cacheEnabled) {
            $this->user->onHook(Model::HOOK_AFTER_SAVE, function (Model $m) {
                $this->cache->setData($m->get());
            });
        }

        // logout if requested
        if (isset($_GET['logout'])) {
            $this->logout();
            $this->getApp()->redirect([$this->pageExit]);
        }

        // validate user
        if ($this->check) {
            $this->check();
        }

        return $this;
    }

    /**
     * Load data from cache.
     */
    protected function loadFromCache(): void
    {
        $cacheData = $this->cache->getData();
        if (isset($cacheData[$this->user->idField])) {
            $this->user = $this->user->getModel()->load($cacheData[$this->user->idField]);
        }
        $this->user->setMulti($cacheData);
    }

    /**
     * Is logged in.
     */
    public function isLoggedIn(): bool
    {
        return $this->user->isLoaded();
    }

    /**
     * Try to log in user.
     */
    public function tryLogin(string $email, string $password): bool
    {
        // first logout
        $this->logout();

        $userModel = new $this->user($this->user->getModel()->getPersistence());

        $userEntity = $userModel->tryLoadBy($this->fieldLogin, $email);
        if ($userEntity !== null) {
            // verify if the password matches
            $passwordField = PasswordField::assertInstanceOf($userEntity->getField($this->fieldPassword));
            if ($passwordField->verifyPassword($userEntity, $password)) {
                $this->hook(self::HOOK_LOGGED_IN, [$userEntity]);
                // save user record in cache
                if ($this->cacheEnabled) {
                    $this->cache->setData($userEntity->get());
                    $this->loadFromCache();
                } else {
                    $this->user = clone $userEntity;
                }

                return true;
            }
            $this->hook(self::HOOK_BAD_LOGIN, [$email]);
        }

        return false;
    }

    /**
     * Logout user.
     */
    public function logout(): void
    {
        if ($this->isLoggedIn()) {
            $this->user->unload();
        }
        if ($this->cacheEnabled) {
            $this->cache->setData([]);
        }
    }

    /**
     * Link ACL object with this Auth controller object, apply restrictions on user model and
     * also apply ACL restrictions on each model you add to this persistence in future.
     *
     * @param Persistence $persistence Optional persistence, use User model persistence by default
     *
     * @return $this
     */
    public function setAcl(Acl $acl, Persistence $persistence = null)
    {
        $persistence ??= $this->user->getModel()->getPersistence();
        $acl->auth = $this;
        $acl->applyRestrictions($this->user);

        $persistence->onHook(Persistence::HOOK_AFTER_ADD, static function (Persistence $p, Model $m) use ($acl) {
            $acl->applyRestrictions($m);
        });

        return $this;
    }

    /**
     * Call this method to verify credentials.
     *
     * It will show login form in case user is not already logged in.
     */
    public function check(): void
    {
        if ($this->isLoggedIn()) {
            // if user is already logged in
            $this->addUserMenu();
        } else {
            // if user is not logged in, then show login form
            $this->displayLoginForm();
        }
    }

    /**
     * Adds user dropdown menu in apps right menu.
     */
    public function addUserMenu(): void
    {
        // add admin menu
        if ($this->hasUserMenu && $this->getApp()->layout instanceof Layout\Admin) {
            $menu = $this->getApp()->layout->menuRight->addMenu($this->user->getTitle());

            if ($this->hasPreferences) {
                $userPage = VirtualPage::addToWithCl($this->getApp(), $this->preferencePage);
                $this->setPreferencePage($userPage);

                $menu->addItem(['Preferences', 'icon' => 'user'], $userPage->getUrl());
            }

            $menu->addItem(['Logout', 'icon' => 'sign out'], [$this->pageDashboard, 'logout' => 1]);
        }
    }

    /**
     * Set preference page content.
     */
    public function setPreferencePage(VirtualPage $page): void
    {
        $f = Form::addTo($page);
        $f->addHeader(['User Preferences', 'subHeader' => $this->user->getTitle(), 'icon' => 'user']);
        $f->setModel($this->user);
        $f->onSubmit(static function (Form $f) {
            $f->model->save();

            return $f->jsSuccess('User preferences saved.');
        });
    }

    /**
     * Displays only login form in app.
     */
    public function displayLoginForm(array $seed = []): void
    {
        $app = $this->getApp();

        $app->html = null;
        $app->initLayout([Narrow::class]);
        $app->title .= ' - Login Required';
        $app->layout->template->set('title', $app->title);
        $app->add(Factory::factory($this->formLoginSeed, array_merge([
            'auth' => $this,
            'linkSuccess' => [$this->pageAfterLogin],
            'linkForgot' => false,
        ], $seed)));
        $app->run();
        $app->callExit();
    }
}
