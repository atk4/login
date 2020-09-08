<?php

declare(strict_types=1);

namespace atk4\login;

use atk4\ui\Form;
use atk4\ui\Form\Control;
use atk4\ui\View;

/**
 * Login form view.
 */
class LoginForm extends Form
{
    /** @var array "Forgot password" page */
    public $linkForgot = ['forgot'];

    /** @var array "Dashboard" page */
    public $linkSuccess = ['dashboard'];

    /** @var Auth object */
    public $auth;

    /** @var false|string show cookie warning? */
    public $cookieWarning = 'This website uses web cookie to remember you while you are logged in.';

    /**
     * Intialization.
     */
    protected function init(): void
    {
        parent::init();

        $form = $this;

        $form->buttonSave->set('Sign in');
        $form->buttonSave->addClass('large fluid');
        $form->buttonSave->iconRight = 'right arrow';

        $form->addControl('email', null, ['required' => true]);
        $p = $form->addControl('password', [Control\Password::class], ['required' => true]);

        if ($this->linkForgot) {
            $p->addAction(['icon' => 'question'])
                ->setAttr('title', 'Forgot your password?')
                ->link($this->linkForgot);
        }

        if ($this->cookieWarning) {
            View::addTo($form, ['element' => 'p'])
                ->addStyle('font-style', 'italic')
                ->set($this->cookieWarning);
        }

        if ($this->auth) {
            $this->onSubmit(function ($form) {
                // try to log user in
                if ($this->auth->tryLogin($form->model->get('email'), $form->model->get('password'))) {
                    return $this->app->jsRedirect($this->linkSuccess);
                }

                return $form->error('password', 'Email or Password is incorrect');
            });
        }
    }
}
