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

    public function setModel(Model $user, array $fields = null): void
    {
        parent::setModel($user, []);

        $form = $this;
        $form->addControl('name', [], ['required' => true]);
        $form->addControl('email', [], ['required' => true]);
        $form->addControl('password', [], ['type' => 'string', 'required' => true])
            ->setInputAttr('autocomplete', 'new-password');
        $form->addControl('password2', [], ['type' => 'string', 'neverPersist' => true, 'required' => true, 'caption' => 'Repeat Password'])
            ->setInputAttr('autocomplete', 'new-password');

        // on form submit save new user in persistence
        $form->onSubmit(function ($form) {
            // Look if user already exist?
            $model = $this->model->getModel();
            $entity = $model->tryLoadBy($this->auth->fieldLogin, $form->model->get($this->auth->fieldLogin));
            if ($entity !== null) {
                return $form->error($this->auth->fieldLogin, 'User with this email already exist');
            }

            // check if passwords match
            if (!$form->model->getField('password')->verifyPassword($form->model, $form->model->get('password2'))) {
                return $form->error('password2', 'Passwords does not match');
            }

            // save user
            $form->model->save();

            return $form->success('Account has been created');
        });
    }
}
