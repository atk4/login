<?php

declare(strict_types=1);

namespace Atk4\Login\Form;

use Atk4\Data\Field\PasswordField;
use Atk4\Data\Model;
use Atk4\Login\Auth;
use Atk4\Ui\Form;
use Atk4\Ui\Form\Control;

/**
 * Register form view.
 */
class Register extends Form
{
    /** @var array Page to redirect after succesful creating of user */
    public $linkSuccess = [null];

    /** @var Auth|null object */
    public $auth;

    #[\Override]
    protected function init(): void
    {
        parent::init();

        $this->buttonSave->set('Register');
        $this->buttonSave->addClass('large fluid');
        $this->buttonSave->iconRight = 'right arrow';

        if ($this->linkSuccess !== [null]) {
            $this->onHook(self::HOOK_DISPLAY_SUCCESS, function () {
                return $this->getApp()->jsRedirect($this->linkSuccess);
            });
        }
    }

    #[\Override]
    public function setModel(Model $user, array $fields = null): void
    {
        parent::setModel($user, []);

        $this->addControl('name', [], ['required' => true]);
        $this->addControl('email', [], ['required' => true]);

        /** @var Control\Input */
        $p1 = $this->addControl('password', [Control\Password::class], ['type' => 'string', 'required' => true]);
        $p1->setInputAttr('autocomplete', 'new-password');

        /** @var Control\Input */
        $p2 = $this->addControl('password2', [Control\Password::class], ['type' => 'string', 'neverPersist' => true, 'required' => true, 'caption' => 'Repeat Password']);
        $p2->setInputAttr('autocomplete', 'new-password');

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
