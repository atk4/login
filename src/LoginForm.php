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
    }

    function setModel(\atk4\data\Model $user) {
        parent::setModel(clone $user, false);

        $form = $this;

        $form->addField('email');
        $p = $form->addField('password', ['Password'], ['required'=>true]);
        $p->addAction(['icon'=>'question'])
            ->setAttr('title', 'Forgot your password?')
            ->link($this->forgotLink);

        if ($this->auth) {

            $this->onSubmit(function($form) {
                if ($this->auth->tryLogin($form->model['email'], $form->model['password'])) {
                    return jsExpression('document.location = []', [$this->app->url($this->successLink)]);
                } else {
                    return $form->error('password', 'Email or Password is incorrect');
                }
            });
        }
    }
}
