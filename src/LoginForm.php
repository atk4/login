<?php
namespace atk4\login;

class LoginForm extends \atk4\ui\Form {
    public $forgotLink = ['forgot'];
    public $successLink = ['dashboard'];

    public $auth = null;

    function init() {
        parent::init();

        $form = $this;

        $form->buttonSave->set('Sign in');
        $form->buttonSave->addClass('large fluid');
        $form->buttonSave->iconRight = 'right arrow';

        $form = $this;

        $form->addField('email', null, ['required'=>true]);
        $p = $form->addField('password', ['Password'], ['required'=>true]);
        $p->addAction(['icon'=>'question'])
            ->setAttr('title', 'Forgot your password?')
            ->link($this->forgotLink);

        if ($this->auth) {

            $this->onSubmit(function($form) {
                if ($this->auth->tryLogin($form->model['email'], $form->model['password'])) {
                    return $this->app->jsRedirect($this->successLink);
                } else {
                    return $form->error('password', 'Email or Password is incorrect');
                }
            });
        }
    }
}
