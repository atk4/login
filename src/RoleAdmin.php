<?php

namespace atk4\login;

use atk4\core\DebugTrait;
use atk4\core\Exception;
use atk4\data\Model;
use atk4\ui\CRUD;
use atk4\ui\Exception\NoRenderTree;
use atk4\ui\View;
use atk4\ui\TableColumn\ActionButtons;

/**
 * View for Role administration.
 * Includes Role association with AccessRule.
 */
class RoleAdmin extends View
{
    // Imants: This class needs better implementation. We can take some ideas from UserAdmin view.
    //         On other hand UserAdmin view will be simplified when RoleAdmin will be developed,
    //         because it's role which have permissions set not User. UserAdmin should just show
    //         permissions it get from roles and nothing more. Maybe not even that!

    use DebugTrait;

    /** @var CRUD */
    public $crud = null;

    /**
     * Initialization.
     * @throws Exception
     */
    public function init(): void
    {
        parent::init();

        //$this->crud = $this->add('CRUD', ['formDefault' => ['Form', 'layout' => 'Columns']]);
        //// @TODO probably need special form here which will add conditional fields - all_visible vs. visible_fields etc.
        $this->crud = $this->add('CRUD');
    }

    /**
     * Initialize User Admin and add all the UI pieces.
     *
     * @param Model $role
     *
     * @return Model
     * @throws \atk4\ui\Exception
     * @throws NoRenderTree
     *
     * @throws Exception
     */
    public function setModel(Model $role)
    {
        // set model for CRUD
        $this->crud->setModel($role);

        // Add new table column used for actions
        /** @var \atk4\ui\TableColumn\Generic $column */
        $column = $this->crud->table->addColumn(null, [ActionButtons::class, 'caption' => '']);

        $column->addModal(['icon' => 'cogs'], 'Role Permissions', function (View $v, $id) {
            $this->model->load($id);

            $v->add(['Header', $this->model->getTitle() . ' Permissions']);

            /** @var CRUD $crud */
            $crud = CRUD::addTo($v);
            $crud->setModel($this->model->ref('AccessRules'));
        });

        //@todo remove this line. It's just a workaround while CRUD edit action button will be fixed in modal windows
        $this->crud->owner->add(['CRUD'])->setModel($this->crud->model->ref('AccessRules'));

        return parent::setModel($role);
    }
}
