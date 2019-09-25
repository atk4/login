<?php
namespace atk4\login;

use atk4\data\Model;
use atk4\ui\CRUD;
use atk4\ui\View;

/**
 * View for user administration.
 */
class UserAdmin extends View
{
    use \atk4\core\DebugTrait;

    /** @var \atk4\ui\CRUD */
    public $crud = null;

    /**
     * Initialization.
     */
    public function init()
    {
        parent::init();

        $this->crud = $this->add('CRUD');
    }

    /**
     * Initialize User Admin and add all the UI pieces.
     *
     * @param Model $user
     *
     * @return Model
     */
    public function setModel(Model $user)
    {

        $this->crud->setModel($user);


        // Add new table column used for actions
        $a = $this->crud->table->addColumn(null, ['Actions', 'caption'=>'User Actions']);

        // Pop-up for resetting password. Will display button for generating random password
        $a->addModal(['icon'=>'key'], 'Change Password', function($v, $id) {

            $this->model->load($id);

            $form = $v->add('Form');
            $f = $form->addField('visible_password', null, ['required'=>true]);
            //$form->addField('email_user', null, ['type'=>'boolean', 'caption'=>'Email user their new password']);

            $f->addAction(['icon'=>'random'])->on('click', function() use ($f) {
                return $f->jsInput()->val($this->model->getField('password')->suggestPassword());
            });

            $form->onSubmit(function($form) use ($v) {
                $this->model['password'] = $form->model['visible_password'];
                $this->model->save();

                return [
                    $v->owner->hide(),
                    $this->notify = new \atk4\ui\jsNotify([
                        'content' => 'Password for '.$this->model[$this->model->title_field].' is changed!',
                        'color'   => 'green',
                    ])
                ];

                //return 'Setting '.$form->model['visible_password'].' for '.$this->model['name'];
            });

        })->setAttr('title', 'Change Password');

        $a->addModal(['icon'=>'eye'], 'Details', function($v, $id) {
            $this->model->load($id);

            $c = $v->add('Columns');

            /** @var \atk4\ui\View $left */
            $left = $c->addColumn();

            /** @var \atk4\ui\View $right */
            $right = $c->addColumn();

            $left->add(['Header', 'Role "'.$this->model['role'].'" Access']);
            /** @var CRUD $crud */
            $crud = $left->add(['CRUD']);
            $crud->setModel($this->model->ref('AccessRules'));
            $crud->table->onRowClick($right->jsReload(['rule'=>$crud->table->jsRow()->data('id')]));

            $right->add(['Header', 'Role Details']);
            $rule = $right->stickyGet('rule');
            if (!$rule) {
                $right->add(['Message', 'Select role on the left', 'yellow']);
            } else {
                $right->add('CRUD')->setModel($this->model->ref('AccessRules')->load($rule)->ref('model_defs'));
            }

        })->setAttr('title', 'User Details');

        return parent::setModel($user);
    }
}
