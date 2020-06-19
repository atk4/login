<?php

declare(strict_types=1);

namespace atk4\login;

/**
 * Login form view.
 */
class LoginForm extends \atk4\ui\Form
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
    public function init(): void
    {
        parent::init();

        $form = $this;

        $form->buttonSave->set('Sign in');
        $form->buttonSave->addClass('large fluid');
        $form->buttonSave->iconRight = 'right arrow';

        $form->addField('email', null, ['required' => true]);
        $p = $form->addField('password', new \atk4\ui\FormField\Password(), ['required' => true]);

        if ($this->linkForgot) {
            $p->addAction(['icon' => 'question'])
                ->setAttr('title', 'Forgot your password?')
                ->link($this->linkForgot);
        }

        if ($this->cookieWarning) {
            \atk4\ui\View::addTo($form, ['element' => 'p'])
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
