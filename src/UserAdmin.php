<?php

declare(strict_types=1);

namespace atk4\login;

use atk4\data\Model;
use atk4\ui\Crud;
use atk4\ui\Form;
use atk4\ui\jsToast;
use atk4\ui\Table\Column\ActionButtons;
use atk4\ui\View;

/**
 * View for User administration.
 * Includes User association with Role.
 */
class UserAdmin extends View
{
    use \atk4\core\DebugTrait;

    /** @var Crud */
    public $crud;

    /**
     * Initialization.
     */
    protected function init(): void
    {
        parent::init();

        $this->crud = Crud::addTo($this);
    }

    /**
     * Initialize User Admin and add all the UI pieces.
     *
     * @return Model
     */
    public function setModel(Model $user)
    {
        //$user->getUserAction('register_new_user')->system = true;
        $user->getUserAction('add')->system = true;

        // set model for CRUD
        $this->crud->setModel($user);

        // Add new table column used for actions
        $column = $this->crud->table->addColumn(null, [ActionButtons::class, 'caption' => '']);

        // Pop-up for resetting password. Will display button for generating random password
        $column->addModal(['icon' => 'key'], 'Change Password', function ($v, $id) {
            $this->model->load($id);

            $form = $v->add([Form::class]);
            $f = $form->addControl('visible_password', null, ['required' => true]);
            //$form->addControl('email_user', null, ['type'=>'boolean', 'caption'=>'Email user their new password']);

            $f->addAction(['icon' => 'random'])->on('click', function () use ($f) {
                return $f->jsInput()->val($this->model->getField('password')->suggestPassword());
            });

            $form->onSubmit(function ($form) use ($v) {
                $this->model->set('password', $form->model->get('visible_password'));
                $this->model->save();

                return [
                    $v->owner->hide(),
                    new jsToast([
                        'message' => 'Password for ' . $this->model->get($this->model->title_field) . ' is changed!',
                        'class' => 'success',
                    ]),
                ];

                //return 'Setting '.$form->model['visible_password'].' for '.$this->model['name'];
            });
        });

        /*
        $column->addModal(['icon'=>'eye'], 'Details', function($v, $id) {
            $this->model->load($id);

            $c = $v->add(Columns::class);
            $left = $c->addColumn();
            $right = $c->addColumn();

            $left->add([Header::class, 'Role "'.$this->model['role'].'" Access']);
            $crud = $left->add([CRUD::class]);
            $crud->setModel($this->model->ref('AccessRules'));
            $crud->table->onRowClick($right->jsReload(['rule'=>$crud->table->jsRow()->data('id')]));

            $right->add([Header::class, 'Role Details']);
            $rule = $right->stickyGet('rule');
            if (!$rule) {
                $right->add([Message::class, 'Select role on the left', 'yellow']);
            } else {
                $right->add([CRUD::class])->setModel($this->model->ref('AccessRules')->load($rule));
            }
        })->setAttr('title', 'User Details');
        */

        return parent::setModel($user);
    }
}
