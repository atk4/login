<?php
namespace atk4\login;

/**
 * Register form view.
 */
class RegisterForm extends \atk4\ui\Form
{
    /**
     * Which field to look up user by.
     *
     * @var string
     */
    public $fieldLogin = 'email';

    /**
     * Initialization.
     */
    public function init()
    {
        parent::init();

        $form = $this;

        $form->buttonSave->set('Sign in');
        $form->buttonSave->addClass('large fluid');
        $form->buttonSave->iconRight = 'right arrow';
    }

    /**
     * Sets user model.
     *
     * @param \atk4\data\Model $user
     * @param array            $fields
     *
     * @return \atk4\data\Model
     */
    public function setModel(\atk4\data\Model $user, $fields = null)
    {
        parent::setModel($user, false);

        $form = $this;
        $form->addField('email', null, ['required' => 'true']);
        $f = $form->addField('password', ['Password'], ['required' => true]);
        $form->addField('password2', ['Password'], ['required' => true, 'caption' => 'Repeat Password', 'never_persist' => true]);

        // on form submit save new user in persistence
        $form->onSubmit(function($form) {

            // Look if user already exist?
            $c = clone $this->model;
            $c->unload();
            $c->tryLoadBy($this->fieldLogin, strtolower($form->model['email']));
            if ($c->loaded()) {
                return $form->error('email', 'User with this email already exist');
            }

            // check if passwords match
            if ($form->model['password'] != $form->model['password2']) {
                return $form->error('password2', 'Passwords does not match');
            }

            // save user
            $form->model->save();

            return $form->success('Account has been created');
        });

        return $form->model;
    }
}
