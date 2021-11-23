<?php

declare(strict_types=1);

namespace Atk4\Login\Form;

use Atk4\Login\Auth;
use Atk4\Ui\Form;
use Atk4\Ui\Form\Control;
use Atk4\Ui\View;

/**
 * Login form view.
 */
class Login extends Form
{
    /** @var array "Forgot password" page */
    public $linkForgot = ['forgot'];

    /** @var array "Dashboard" page */
    public $linkSuccess = ['dashboard'];

    /** @var Auth|null object */
    public $auth;

    /** @var string|false show cookie warning? */
    public $cookieWarning = 'This website uses web cookie to remember you while you are logged in.';

    protected function init(): void
    {
        parent::init();

        $form = $this;

        $form->buttonSave->set('Sign in');
        $form->buttonSave->addClass('large fluid');
        $form->buttonSave->iconRight = 'right arrow';

        $form->addControl($this->auth->fieldLogin, [], ['required' => true]);
        $p = $form->addControl($this->auth->fieldPassword, [Control\Password::class], ['required' => true]);

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
                if ($this->auth->tryLogin($form->model->get($this->auth->fieldLogin), $form->model->get($this->auth->fieldPassword))) {
                    return $this->getApp()->jsRedirect($this->linkSuccess);
                }

                return $form->error('password', 'Email or password is incorrect');
            });
        }
    }
}
