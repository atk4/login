<?php

declare(strict_types=1);

namespace Atk4\Login;

use Atk4\Core\DebugTrait;
use Atk4\Data\Field\PasswordField;
use Atk4\Data\Model;
use Atk4\Ui\Crud;
use Atk4\Ui\Form;
use Atk4\Ui\JsToast;
use Atk4\Ui\Table\Column\ActionButtons;
use Atk4\Ui\View;

/**
 * View for User administration.
 * Includes User association with Role.
 */
class UserAdmin extends View
{
    use DebugTrait;

    /** @var Crud */
    public $crud;

    protected function init(): void
    {
        parent::init();

        $this->crud = Crud::addTo($this);
    }

    /**
     * Initialize User Admin and add all the UI pieces.
     */
    public function setModel(Model $user): void
    {
        //$user->getUserAction('register_new_user')->system = true;
        $user->getUserAction('add')->system = true;

        // set model for CRUD
        $this->crud->setModel($user);

        // Add new table column used for actions
        $column = $this->crud->table->addColumn(null, [ActionButtons::class, 'caption' => '']);

        // Pop-up for resetting password. Will display button for generating random password
        $column->addModal(['icon' => 'key'], 'Change Password', function ($v, $id) {
            $userEntity = $this->model->load($id);

            $form = Form::addTo($v);
            $f = $form->addControl('visible_password', [], ['required' => true]);
            //$form->addControl('email_user', [], ['type'=>'boolean', 'caption' => 'Email user their new password']);

            $f->addAction(['icon' => 'random'])->on('click', function () use ($f, $userEntity) {
                return $f->jsInput()->val(PasswordField::assertInstanceOf($userEntity->getField('password'))->generatePassword());
            });

            $form->onSubmit(function ($form) use ($v, $userEntity) {
                PasswordField::assertInstanceOf($userEntity->getField('password'))
                    ->setPassword($userEntity, $form->model->get('visible_password'));
                $userEntity->save();

                return [
                    $v->getOwner()->hide(),
                    new JsToast([
                        'message' => 'Password for ' . $userEntity->get($userEntity->title_field) . ' is changed!',
                        'class' => 'success',
                    ]),
                ];
            });
        });

        /*
        $column->addModal(['icon' => 'eye'], 'Details', function($v, $id, $userEntity) {
            $userEntity = $this->model->load($id);

            $c = Columns::addTo($v);
            $left = $c->addColumn();
            $right = $c->addColumn();

            Header::addTo($left, ['Role "' . $userEntity['role'] . '" Access']);
            $crud = Crud::addTo($left);
            $crud->setModel($userEntity->ref('AccessRules'));
            $crud->table->onRowClick($right->jsReload(['rule'=>$crud->table->jsRow()->data('id')]));

            Header::addTo($right, ['Role Details']);
            $rule = $right->stickyGet('rule');
            if (!$rule) {
                Message::addTo($right, ['Select role on the left', 'yellow']);
            } else {
                Crud::addTo($right)->setModel($userEntity->ref('AccessRules')->load($rule));
            }
        })->setAttr('title', 'User Details');
        */

        parent::setModel($user);
    }
}
