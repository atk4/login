<?php

declare(strict_types=1);

namespace atk4\login;

use atk4\core\AppScopeTrait;
use atk4\core\ContainerTrait;
use atk4\core\DiContainerTrait;
use atk4\core\FactoryTrait;
use atk4\core\HookTrait;
use atk4\core\InitializerTrait;
use atk4\core\SessionTrait;
use atk4\core\TrackableTrait;
use atk4\data\Model;
use atk4\data\Persistence;
use atk4\login\Layout\Narrow;
use atk4\ui\Form;
use atk4\ui\Header;
use atk4\ui\Layout\Admin;
use atk4\ui\VirtualPage;

/**
 * Authentication controller. Add this to your application somewhere
 * and it will work wonders.
 */
class Auth
{
    use SessionTrait;
    use ContainerTrait;
    use FactoryTrait;
    use AppScopeTrait;
    use DiContainerTrait;
    use TrackableTrait;
    use HookTrait;
    use InitializerTrait {
        init as _init;
    }

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
     * Login Form. If you want to use a different LoginForm you can pass
     * a seed or object here.
     *
     * @var string|Form
     */
    public $form = LoginForm::class;

    /**
     * @var array Seed that would create VirtualPage for adding Preference page content
     */
    public $preferencePage = [VirtualPage::class, 'appStickyCb' => false];

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
     * Permorm check automatically and display a Login form when 'setModel' takes place.
     *
     * This is a transparent way to add authentication to an existing application.
     *
     * @var bool
     */
    public $check = true;

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
    }

    /**
     * Initialization.
     */
    protected function init(): void
    {
        $this->_init();
        $this->startSession();
    }

    /**
     * Return session persistence object.
     *
     * @return Persistence\Array_
     */
    public function getSessionPersistence()
    {
        $this->startSession();
        $key = $this->name;

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }

        $p = new Persistence\Array_();
        \Closure::bind(function () use ($p, $key) {$p->data = &$_SESSION[$key]; }, null, Persistence\Array_::class)();

        return $p;
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
    public function setModel($model, $fieldLogin = null, $fieldPassword = null)
    {
        $this->user = $model;

        if ($fieldLogin) {
            $this->fieldLogin = $fieldLogin;
        }

        if ($fieldPassword) {
            $this->fieldPassword = $fieldPassword;
        }

        $this->user->data = $this->getSessionPersistence()->tryLoad($this->user, 1) ?: [];
        $this->user->id = $this->user->data ? $this->user->data[$this->user->id_field] : null;

        // update session persistence after changes saved in user model
        $this->user->onHook(Model::HOOK_AFTER_SAVE, function ($m) {
            $this->getSessionPersistence()->update($m, 1, $m->get());
        });

        // validate user
        if ($this->check) {
            $this->check();
        }

        return $this;
    }

    /**
     * Link ACL object with this Auth controller object, apply restrictions on user model and
     * also apply ACL restrictions on each model you add to this persistence in future.
     *
     * @param Persistence $persistence Optional persistence, use User model persistence by default
     *
     * @return $this
     */
    public function setACL(ACL $acl, Persistence $persistence = null)
    {
        $persistence = $persistence ?? $this->user->persistence;
        $acl->auth = $this;
        $acl->applyRestrictions($this->user->persistence, $this->user);

        $persistence->onHook(Persistence::HOOK_AFTER_ADD, \Closure::fromCallable([$acl, 'applyRestrictions']));

        return $this;
    }

    /**
     * Logout user.
     */
    public function logout()
    {
        $this->getSessionPersistence()->delete($this->user, 1);
    }

    /**
     * Call this method to verify credentials.
     *
     * It will show login form in case user is not already logged in.
     */
    public function check()
    {
        if ($this->user->loaded()) {
            // if user is already logged in
            $this->addUserMenu();
        } else {
            // if user is not logged in, then show login form
            $this->displayLoginForm();
        }
    }

    public function addUserMenu()
    {
        // add admin menu
        if ($this->hasUserMenu && $this->app->layout instanceof Admin) {
            $m = $this->app->layout->menuRight->addMenu($this->user->getTitle());

            if ($this->hasPreferences) {
                $userPage = $this->app->add($this->preferencePage);
                $this->setPreferencePage($userPage);

                $m->addItem(['Preferences', 'icon' => 'user'], [$userPage->getUrl()]);
            }

            $m->addItem(['Logout', 'icon' => 'sign out'], [$this->pageDashboard, 'logout' => true]);
        }

        if (isset($_GET['logout'])) {
            $this->logout();
            $this->app->redirect([$this->pageExit]);
        }
    }

    /**
     * Set preference page content.
     */
    public function setPreferencePage(VirtualPage $page)
    {
        $page->add([Header::class, 'User Preferences', 'subHeader' => $this->user->getTitle(), 'icon' => 'user']);
        $page->add([Form::class])->setModel($this->user);
    }

    public function displayLoginForm()
    {
        $this->app->catch_runaway_callbacks = false;
        $this->app->html = null;
        $this->app->initLayout(new Narrow());
        $this->app->title = $this->app->title . ' - Log-in Required';
        $this->app->add([
            $this->form,
            'auth' => $this,
            'linkSuccess' => [$this->pageDashboard],
            'linkForgot' => false,
        ]);
        $this->app->layout->template->set('title', $this->app->title);
        $this->app->run();
        $this->app->callExit();
    }

    /**
     * Try to log in user.
     *
     * @param string $email
     * @param string $password
     *
     * @return bool
     */
    public function tryLogin($email, $password)
    {
        $user = clone $this->user;
        $user->unload();

        $user->tryLoadBy($this->fieldLogin, $email);
        if ($user->loaded()) {
            // verify if the password matches
            if ($user->compare($this->fieldPassword, $password)) {
                $this->hook(self::HOOK_LOGGED_IN, [$user]);
                $this->getSessionPersistence()->update($user, 1, $user->get());

                return true;
            }
            $this->hook(self::HOOK_BAD_LOGIN, [$email]);
        }

        return false;
    }
}
