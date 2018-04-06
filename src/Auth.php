<?php

namespace atk4\login;

/**
 * Authentication controller. Add this to your application somewhere
 * and it will work wonders
 */
class Auth {

    use \atk4\core\SessionTrait;
    use \atk4\core\ContainerTrait;
    use \atk4\core\FactoryTrait;
    use \atk4\core\AppScopeTrait;
    use \atk4\core\DIContainerTrait;
    use \atk4\core\TrackableTrait;
    use \atk4\core\InitializerTrait {
        init as _init;   
    }

    /**
     * Contains information about a current user. Unlike Model this will
     * contain a record loaded from session cache
     */
    public $user = null;

    /**
     * Login Form. If you want to use a different LoginForm you can pass
     * a seed or object here.
     */
    public $form = '\atk4\login\LoginForm';

    /**
     * Which field to look up user by
     */
    public $fieldLogin = 'email';

    /**
     * Password to be verified when authenticating
     */
    public $fieldPassword = 'password';

    /**
     * Permorm check automatically and display a Login form when 'setModel' takes place. 
     * 
     * This is a transparent way to add authentication to an existing application
     */
    public $check = true;

    /**
     * Which is the index page? This page should have auth / check.
     */
    public $pageDashboard = null;

    /**
     * User will be sent to exit page when he logs out
     */
    public $pageExit = 'index';

    /**
     * Should we add User Menu to Admin layout?
     */
    public $hasUserMenu = true;

    /**
     * Should we display and handle preferences link
     */
    public $hasPreferences = true;

    function __construct($options = [])
    {
        $this->setDefaults($options);
    }

    function init()
    {
        $this->_init();
        session_start();
    }

    function getSessionPersistence()
    {

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return new \atk4\data\Persistence_Array($_SESSION[$this->name]);
    }

    /**
     * Specify a model for a user check here
     *
     */
    function setModel($model, $login_field = null, $password_field = null)
    {
        $this->user = $model;
        if ($login_field) {
            $this->fieldLogin = $login_field;
        }

        if ($password_field) {
            $this->fieldPassword = $password_field;
        }

        $this->user->data = $this->getSessionPersistence()->tryLoad($this->user, 1);
        $this->user->id = $this->user->data[$this->user->id_field];
        $this->user->addHook('afterSave', function($m) {
            $this->getSessionPersistence()->update($m, 1, $m->get());
            // update persistence

        });

        if ($this->check) {
            $this->check();
        }
    }

    function logout()
    {
        $this->getSessionPersistence()->delete($this->user, 1);
    }

    /**
     * Call this method to verify credentials
     */
    function check($build_form_callback = null)
    {
        if ($this->user->loaded()) {

            if ($this->hasUserMenu && $this->app->layout instanceof \atk4\ui\Layout\Admin) {
                $m = $this->app->layout->menuRight->addMenu($this->user->getTitle());

                if ($this->hasPreferences) {
                    $m->addItem(['Preferences', 'icon'=>'user'], [$this->pageDashboard, 'preferences'=>true]);
                }

                $m->addItem(['Logout', 'icon'=>'sign out'], [$this->pageDashboard, 'logout'=>true]);
            }


            if ($this->hasPreferences && $this->app->stickyGet('preferences')) {
                $this->app->add(['Header', 'User Preferences', 'subHeader'=>$this->user->getTitle(), 'icon'=>'user']);
                $this->app->add('Form')->setModel($this->user);
                exit;
            }

            if (isset($_GET['logout'])) {
                $this->logout();
                $this->app->redirect([$this->pageExit]);
            }

            return;
        }

        $l = new \atk4\ui\App();
        $this->app->catch_runaway_callbacks = false;
        $this->app->run_called = true;
        $l->catch_runaway_callbacks = false;
        $l->initLayout(new \atk4\login\Layout\Narrow());

        $form = $l->add([
            $this->form, 
            'auth'=>$this, 
            'linkSuccess'=>[$this->pageDashboard],
            'linkForgot'=>false,
        ]);

        $l->layout->template->set('title', 'Log-in Required');


        $l->run();
        $this->app->terminate(); 
        exit;
    }

    function tryLogin($email, $password) {
        $user = $this->user->newInstance();

        $user->tryLoadBy($this->fieldLogin, $email);
        if ($user->loaded()) {

            // verify if the password matches
            if ($user->compare($this->fieldPassword, $password)) {
                $this->getSessionPersistence()->update($user, 1, $user->get());
                return true;
            }
        }
        return false;
    }

}
