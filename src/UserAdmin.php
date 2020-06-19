<?php

declare(strict_types=1);

namespace atk4\login;

use atk4\data\Model;
use atk4\ui\CRUD;
use atk4\ui\View;
use atk4\ui\TableColumn\ActionButtons;

/**
 * View for User administration.
 * Includes User association with Role.
 */
class UserAdmin extends View
{
    use \atk4\core\DebugTrait;

    /** @var CRUD */
    public $crud = null;

    /**
     * Initialization.
     */
    public function init(): void
    {
        parent::init();

        $this->crud = \atk4\ui\CRUD::addTo($this);
    }

    /**
     * Initialize User Admin and add all the UI pieces.
     *
     * @param Model $user
     *
     * @throws \atk4\core\Exception
     * @throws \atk4\ui\Exception
     * @throws \atk4\ui\Exception\NoRenderTree
     *
     * @return Model
     */
    public function setModel(Model $user)
    {
        //$user->getAction('register_new_user')->system = true;
        $user->getAction('add')->system = true;

        // set model for CRUD
        $this->crud->setModel($user);

        // Add new table column used for actions
        $column = $this->crud->table->addColumn(null, [ActionButtons::class, 'caption'=>'']);

        // Pop-up for resetting password. Will display button for generating random password
        $column->addModal(['icon'=>'key'], 'Change Password', function ($v, $id) {
            $this->model->load($id);

            $form = $v->add('Form');
            $f = $form->addField('visible_password', null, ['required'=>true]);
            //$form->addField('email_user', null, ['type'=>'boolean', 'caption'=>'Email user their new password']);

            $f->addAction(['icon'=>'random'])->on('click', function () use ($f) {
                return $f->jsInput()->val($this->model->getField('password')->suggestPassword());
            });

            $form->onSubmit(function ($form) use ($v) {
                $this->model->set('password', $form->model->get('visible_password'));
                $this->model->save();

                return [
                    $v->owner->hide(),
                    $this->notify = new \atk4\ui\jsNotify([
                        'content' => 'Password for ' . $this->model->get($this->model->title_field) . ' is changed!',
                        'color'   => 'green',
                    ])
                ];

                //return 'Setting '.$form->model['visible_password'].' for '.$this->model['name'];
            });
        });

        /*
        $column->addModal(['icon'=>'eye'], 'Details', function($v, $id) {
            $this->model->load($id);

            $c = $v->add('Columns');
            $left = $c->addColumn();
            $right = $c->addColumn();

            $left->add(['Header', 'Role "'.$this->model['role'].'" Access']);
            $crud = $left->add(['CRUD']);
            $crud->setModel($this->model->ref('AccessRules'));
            $crud->table->onRowClick($right->jsReload(['rule'=>$crud->table->jsRow()->data('id')]));

            $right->add(['Header', 'Role Details']);
            $rule = $right->stickyGet('rule');
            if (!$rule) {
                $right->add(['Message', 'Select role on the left', 'yellow']);
            } else {
                $right->add('CRUD')->setModel($this->model->ref('AccessRules')->load($rule));
            }
        })->setAttr('title', 'User Details');
        */

        return parent::setModel($user);
    }
}
