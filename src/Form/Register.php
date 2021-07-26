<?php

declare(strict_types=1);

namespace Atk4\Login\Form;

use Atk4\Data\Model;
use Atk4\Login\Auth;
use Atk4\Ui\Form;

/**
 * Register form view.
 */
class Register extends Form
{
    /** @var Auth object */
    public $auth;

    protected function init(): void
    {
        parent::init();

        $form = $this;

        $form->buttonSave->set('Register');
        $form->buttonSave->addClass('large fluid');
        $form->buttonSave->iconRight = 'right arrow';
    }

    /**
     * Sets user model.
     *
     * @param array $fields
     *
     * @return Model
     */
    public function setModel(Model $user, $fields = null)
    {
        parent::setModel($user, false);

        $form = $this;
        $form->addControl('name', null, ['required' => 'true']);
        $form->addControl('email', null, ['required' => 'true']);
        $form->addControl('password', null, ['type' => 'password', 'required' => true])
            ->setInputAttr('autocomplete', 'new-password');
        $form->addControl('password2', null, ['type' => 'password', 'required' => true, 'caption' => 'Repeat Password', 'never_persist' => true])
            ->setInputAttr('autocomplete', 'new-password');

        // on form submit save new user in persistence
        $form->onSubmit(function ($form) {
            // Look if user already exist?
            $c = clone $this->model;
            $c->unload();
            $c->tryLoadBy($this->auth->fieldLogin, strtolower($form->model->get($this->auth->fieldLogin)));
            if ($c->loaded()) {
                return $form->error($this->auth->fieldLogin, 'User with this email already exist');
            }

            // check if passwords match
            if ($form->model->get('password') !== $form->model->get('password2')) {
                return $form->error('password2', 'Passwords does not match');
            }

            // save user
            $form->model->save();

            return $form->success('Account has been created');
        });

        return $form->model;
    }
}
