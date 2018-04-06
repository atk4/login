<?php

namespace atk4\login;

/**
 * Authentication controller. Add this to your application somewhere
 * and it will work wonders
 */
class Login {

    use \atk4\core\SessionTrait;
    use \atk4\core\ContainerTrait;
    use \atk4\core\FactoryTrait;
    use \atk4\core\AppScopeTrait;
    use \atk4\core\TrackableTrait;
    use \atk4\core\InitializerTrait {
        init as _init;   
    }


    public $user = null;

    public $form = 'atk4\login\LoginForm';

    public $fieldLogin = 'email';

    public $fieldPassword = 'password';

    function init()
    {
        $this->_init();
        session_start();
    }

    function getSessionPersistence()
    {
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
        $user_data = $this->recall('user_data', null);
        if ($user_data) {
            $this->user->set($user_data);
        } else {
            // Display login form here

            $this->form->onSubmit(function($form) {


                $this->form = $this->factory($this->form);
                $this->form->addField('login', null, $this->user->getElement($this->fieldLogin));
                $this->form->addField('password', null, $this->user->getElement($this->fieldPassword));

                if ($build_form_callback) { 
                    call_user_func($build_form_callback, $this->form);
                }

                // callback may also define onSubmit, in which case ours shouldn't work.
                $this->form->onSubmit(function($form) {
                    $user = clone $this->user;  //dont want to reset it

                    $user->tryLoadBy($this->fieldLogin, $form->model['login']);
                    if ($user->loaded()) {

                        // verify if the password matches
                        if ($user->verify($form->fieldPassword, $form->model['password'])) {
                            return $form->success('user is correct');
                        }
                        return $form->error('login', 'password incorrect');
                    }
                    return $form->error('login', 'no such login');
                });
            });
        }

        return $this;
    }

    function tryLogin($email, $password) {
        $user = new Model\User($this->app->db);  //dont want to reset it

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
