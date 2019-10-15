<?php
namespace atk4\login;

use atk4\data\Model;
use atk4\ui\CRUD;
use atk4\ui\View;

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

    use \atk4\core\DebugTrait;

    /** @var \atk4\ui\CRUD */
    public $crud = null;

    /**
     * Initialization.
     */
    public function init()
    {
        parent::init();

        $this->crud = $this->add('CRUD', ['formDefault' => ['Form', 'layout' => 'Columns']]); // @TODO probably need special form here which will add conditional fields - all_visible vs. visible_fields etc.
    }

    /**
     * Initialize User Admin and add all the UI pieces.
     *
     * @param Model $role
     *
     * @return Model
     */
    public function setModel(Model $role)
    {
        // set model for CRUD
        $this->crud->setModel($role);

        // Add new table column used for actions
        $a = $this->crud->table->addColumn(null, ['Actions', 'caption'=>'']);

        $a->addModal(['icon'=>'cogs'], 'Role Permissions', function($v, $id) {
            $this->model->load($id);

            $v->add(['Header', $this->model->getTitle().'" Permissions']);

            /** @var CRUD $crud */
            $crud = $v->add(['CRUD']);
            $crud->setModel($this->model->ref('AccessRules'));

        })->setAttr('title', 'Permissions');

        return parent::setModel($role);
    }
}
