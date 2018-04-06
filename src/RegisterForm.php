<?php
namespace atk4\login;

class RegisterForm extends \atk4\ui\Form {


    function init() {
        parent::init();

        $form = $this;

        $form->buttonSave->set('Sign in');
        $form->buttonSave->addClass('large fluid');
        $form->buttonSave->iconRight = 'right arrow';
    }

    function setModel(\atk4\data\Model $user) {
        parent::setModel($user, false);
        $form = $this;

        $form->addField('email', null, ['required'=>'true']);

        $f=$form->addField('password', ['Password'], ['required'=>true]);
        $form->addField('password2', ['Password'], ['required'=>true, 'caption'=>'Repeat Password', 'never_persist'=>true]);

        $form->onSubmit(function($form) {

            // Look if user already exist?
            $c = clone $this->model;
            $c->unload();
            $c->tryLoadby('email', strtolower($form->model['email']));
            if ($c->loaded()) {
                return $form->error('email', 'User with this email already exist');
            }

            // check if passwords match
            if ($form->model['password'] != $form->model['password2']) {
                return $form->error('password2', 'Does not match');
            }

            $form->model->save();
            return $form->success('Account has been created');
        });
    }
}
