<?php

declare(strict_types=1);

namespace atk4\login;

use atk4\core\AppScopeTrait;
use atk4\core\ContainerTrait;
use atk4\core\DiContainerTrait;
use atk4\core\Factory;
use atk4\core\HookTrait;
use atk4\core\InitializerTrait;
use atk4\core\TrackableTrait;
use atk4\data\Model;
use atk4\data\Persistence;
use atk4\login\Layout\Narrow;
use atk4\ui\Layout\Admin;
use atk4\ui\VirtualPage;

/**
 * Authentication controller. Add this to your application somewhere
 * and it will work wonders.
 */
class Auth
{
    use AppScopeTrait;
    use ContainerTrait;
    use DiContainerTrait;
    use HookTrait;
    use InitializerTrait {
        init as _init;
    }
    use TrackableTrait;

    /** @const string */
    public const HOOK_LOGGED_IN = self::class . '@loggedIn';

    /** @const string */
    public const HOOK_BAD_LOGIN = self::class . '@badLogin';

    /**
     * Contains information about a current user. Unlike Model this will
     * contain a record loaded from session cache.
     *
     * @var Model
     */
    public $user;

    /**
     * Which field to look up user by.
     *
     * @var string
     */
    public $fieldLogin = 'email';

    /**
     * Password to be verified when authenticating.
     *
     * @var string
     */
    public $fieldPassword = 'password';

    /**
     * Perform check automatically and display a Login form when 'setModel' takes place.
     *
     * This is a transparent way to add authentication to an existing application.
     *
     * @var bool
     */
    public $check = true;

    /**
     * Should use some caching (in session for example) or not?
     *
     * @var bool
     */
    public $cacheEnabled = true;

    /**
     * Cache class to use.
     *
     * @var array
     */
    public $cacheClass = [Cache\Session::class];

    /**
     * Options for cache class.
     *
     * @var array
     */
    public $cacheOptions = [];

    /**
     * Cache object.
     *
     * @var Cache
     */
    protected $cache;

    /**
     * Login Form. If you want to use a different LoginForm you can pass
     * a seed here.
     *
     * @var array
     */
    public $formLoginSeed = [Form\Login::class];

    /**
     * @var array Seed that would create VirtualPage for adding Preference page content
     */
    public $preferencePage = [VirtualPage::class];

    /**
     * Which is the index page? This page should have auth / check.
     *
     * @var string
     */
    public $pageDashboard;

    /**
     * User will be sent to exit page when he logs out.
     *
     * @var string
     */
    public $pageExit = 'index';

    /**
     * Should we add User Menu to Admin layout?
     *
     * @var bool
     */
    public $hasUserMenu = true;

    /**
     * Should we display and handle preferences link in user menu?
     *
     * @var bool
     */
    public $hasPreferences = true;

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->setDefaults($options);

        if ($this->cacheEnabled) {
            $this->cache = Factory::factory($this->cacheClass, $this->cacheOptions);
        }
    }

    /**
     * Initialization.
     */
    protected function init(): void
    {
        $this->_init();
    }

    /**
     * Specify a model for a user check here.
     *
     * @param Model  $model
     * @param string $fieldLogin
     * @param string $fieldPassword
     *
     * @return $this
     */
    public function setModel($model, string $fieldLogin = null, string $fieldPassword = null)
    {
        $this->user = $model;

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
            $this->user->onHook(Model::HOOK_AFTER_SAVE, function ($m) {
                $this->cache->setData($m->get());
            });
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
        $this->user->data = $this->cache->getData();
        $this->user->setId($this->user->data[$this->user->id_field] ?? null);
    }

    /**
     * Is logged in.
     */
    public function isLoggedIn(): bool
    {
        return $this->user->loaded();
    }

    /**
     * Try to log in user.
     */
    public function tryLogin(string $email, string $password): bool
    {
        // first logout
        $this->logout();

        $user = $this->user->newInstance();

        $user->tryLoadBy($this->fieldLogin, $email);
        if ($user->loaded()) {
            // verify if the password matches
            $pw_field = $user->getField($this->fieldPassword);
            if (method_exists($pw_field, 'verify') && $pw_field->verify($password)) {
                $this->hook(self::HOOK_LOGGED_IN, [$user]);
                // save user record in cache
                if ($this->cacheEnabled) {
                    $this->cache->setData($user->get());
                    $this->loadFromCache();
                } else {
                    $this->user = clone $user;
                }

                return true;
            }
            $user->unload();
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
        $persistence = $persistence ?? $this->user->persistence;
        $acl->auth = $this;
        $acl->applyRestrictions($this->user->persistence, $this->user);

        $persistence->onHook(Persistence::HOOK_AFTER_ADD, \Closure::fromCallable([$acl, 'applyRestrictions']));

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
        if ($this->hasUserMenu && $this->getApp()->layout instanceof Admin) {
            $menu = $this->getApp()->layout->menuRight->addMenu($this->user->getTitle());

            if ($this->hasPreferences) {
                $userPage = $this->getApp()->add($this->preferencePage);
                $this->setPreferencePage($userPage);

                $menu->addItem(['Preferences', 'icon' => 'user'], $userPage->getUrl());
            }

            $menu->addItem(['Logout', 'icon' => 'sign out'], [$this->pageDashboard, 'logout' => true]);
        }

        if (isset($_GET['logout'])) {
            $this->logout();
            $this->getApp()->redirect([$this->pageExit]);
        }
    }

    /**
     * Set preference page content.
     */
    public function setPreferencePage(VirtualPage $page): void
    {
        $f = \atk4\ui\Form::addTo($page);
        $f->addHeader(['User Preferences', 'subHeader' => $this->user->getTitle(), 'icon' => 'user']);
        $f->setModel($this->user);
    }

    /**
     * Displays only login form in app.
     */
    public function displayLoginForm(array $seed = []): void
    {
        $this->getApp()->catch_runaway_callbacks = false;
        $this->getApp()->html = null;
        $this->getApp()->initLayout([Narrow::class]);
        $this->getApp()->title = $this->getApp()->title . ' - Log-in Required';
        $this->getApp()->add(array_merge(
            $this->formLoginSeed,
            [
                'auth' => $this,
                'linkSuccess' => [$this->pageDashboard],
                'linkForgot' => false,
            ],
            $seed
        ));
        $this->getApp()->layout->template->set('title', $this->getApp()->title);
        $this->getApp()->run();
        $this->getApp()->callExit();
    }
}
