<?php

declare(strict_types=1);

namespace Atk4\Login\Form;

use Atk4\Login\Auth;
use Atk4\Ui\Form;
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

    #[\Override]
    protected function init(): void
    {
        parent::init();

        $this->buttonSave->set('Sign in');
        $this->buttonSave->addClass('large fluid');
        $this->buttonSave->iconRight = 'right arrow';

        $this->addControl($this->auth->fieldLogin, [], ['required' => true]);

        /** @var Form\Control\Password */
        $p = $this->addControl($this->auth->fieldPassword, [Form\Control\Password::class], ['required' => true]);

        if ($this->linkForgot) {
            $p->addAction(['icon' => 'question'])
                ->setAttr('title', 'Forgot your password?')
                ->link($this->linkForgot);
        }

        if ($this->cookieWarning) {
            View::addTo($this, ['element' => 'p'])
                ->setStyle('font-style', 'italic')
                ->set($this->cookieWarning);
        }

        $linkSuccess = $this->linkSuccess;
        if ($linkSuccess === [null]) {
            $linkSuccess = $this->stickyGet('returnUrl');
            if ($linkSuccess === null) {
                $linkSuccess = $this->stickyGet('returnUrl', $_SERVER['REQUEST_URI']);
            }
        }

        if ($this->auth) {
            $this->onSubmit(function (self $form) use ($linkSuccess) {
                // try to log user in
                if ($this->auth->tryLogin($form->model->get($this->auth->fieldLogin), $form->model->get($this->auth->fieldPassword))) {
                    return $this->getApp()->jsRedirect($linkSuccess);
                }

                return $form->jsError('password', 'Email or password is incorrect');
            });
        }
    }
}
