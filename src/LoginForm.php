<?php
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
    public $auth = null;

    /** @var false|string show cookie warning? */
    public $cookieWarning = 'This website uses web cookie to remember you while you are logged in.';

    public $fieldLoginCaption = 'Email';

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

        $form->addField('email', null, ['required' => true, 'caption' => $this->fieldLoginCaption]);
        $p = $form->addField('password', ['Password'], ['required' => true]);

        if ($this->linkForgot) {
            $p->addAction(['icon' => 'question'])
                ->setAttr('title', 'Forgot your password?')
                ->link($this->linkForgot);
        }

        if ($this->cookieWarning) {
            $form->add(['element' => 'p'])
                ->addStyle('font-style', 'italic')
                ->set($this->cookieWarning);
        }

        if ($this->auth) {
            $this->onSubmit(function($form) {
                // try to log user in
                if ($this->auth->tryLogin($form->model['email'], $form->model['password'])) {
                    return $this->app->jsRedirect($this->linkSuccess);
                } else {
                    return $form->error('password', $this->fieldLoginCaption.' or Password is incorrect');
                }
            });
        }
    }
}
