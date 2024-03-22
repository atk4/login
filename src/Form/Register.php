<?php

declare(strict_types=1);

namespace Atk4\Login\Form;

use Atk4\Data\Field\PasswordField;
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

    #[\Override]
    protected function init(): void
    {
        parent::init();

        $this->buttonSave->set('Register');
        $this->buttonSave->addClass('large fluid');
        $this->buttonSave->iconRight = 'right arrow';
    }

    #[\Override]
    public function setModel(Model $user, array $fields = null): void
    {
        parent::setModel($user, []);

        $this->addControl('name', [], ['required' => true]);
        $this->addControl('email', [], ['required' => true]);
        $this->addControl('password', [], ['type' => 'string', 'required' => true])
            ->setInputAttr('autocomplete', 'new-password');
        $this->addControl('password2', [], ['type' => 'string', 'neverPersist' => true, 'required' => true, 'caption' => 'Repeat Password'])
            ->setInputAttr('autocomplete', 'new-password');

        // on form submit save new user in persistence
        $this->onSubmit(function (self $form) {
            // Look if user already exist?
            $model = $this->model->getModel();
            $entity = $model->tryLoadBy($this->auth->fieldLogin, $form->model->get($this->auth->fieldLogin));
            if ($entity !== null) {
                return $form->jsError($this->auth->fieldLogin, 'User with this email already exist');
            }

            // check if passwords match
            if (!PasswordField::assertInstanceOf($form->model->getField('password'))->verifyPassword($form->model, $form->model->get('password2'))) {
                return $form->jsError('password2', 'Passwords does not match');
            }

            // save user
            $form->model->save();

            return $form->jsSuccess('Account has been created');
        });
    }
}
