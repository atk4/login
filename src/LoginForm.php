<?php
namespace atk4\login;

class LoginForm extends \atk4\ui\Form {
    public $linkForgot = ['forgot'];
    public $linkSuccess = ['dashboard'];

    public $auth = null;

    public $cookieWarning = true;

    function init() {
        parent::init();

        $form = $this;

        $form->buttonSave->set('Sign in');
        $form->buttonSave->addClass('large fluid');
        $form->buttonSave->iconRight = 'right arrow';

        $form = $this;

        $form->addField('email', null, ['required'=>true]);
        $p = $form->addField('password', ['Password'], ['required'=>true]);
        if($this->linkForgot) {
            $p->addAction(['icon'=>'question'])
                ->setAttr('title', 'Forgot your password?')
                ->link($this->linkForgot);
        }

        if ($this->cookieWarning) {
            $form->add(['element'=>'p'])->addStyle('font-style', 'italic')
                ->set('This website uses web cookie to remember you while you are logged in.');
        }

        if ($this->auth) {

            $this->onSubmit(function($form) {
                if ($this->auth->tryLogin($form->model['email'], $form->model['password'])) {
                    return $this->app->jsRedirect($this->linkSuccess);
                } else {
                    return $form->error('password', 'Email or Password is incorrect');
                }
            });
        }
    }
}
