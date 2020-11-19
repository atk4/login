<?php

declare(strict_types=1);

namespace atk4\login;

use atk4\core\DebugTrait;
use atk4\data\Model;
use atk4\ui\Crud;
use atk4\ui\Header;
use atk4\ui\Table\Column\ActionButtons;
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

    use DebugTrait;

    /** @var Crud */
    public $crud;

    /**
     * Initialization.
     */
    protected function init(): void
    {
        parent::init();

        //$this->crud = $this->add(CRUD::class, ['formDefault' => ['Form', 'layout' => 'Columns']]);
        //// @TODO probably need special form here which will add conditional fields - all_visible vs. visible_fields etc.
        $this->crud = Crud::addTo($this);
    }

    /**
     * Initialize User Admin and add all the UI pieces.
     *
     * @return Model
     */
    public function setModel(Model $role)
    {
        // set model for CRUD
        $this->crud->setModel($role);

        // Add new table column used for actions
        $column = $this->crud->table->addColumn(null, [ActionButtons::class, 'caption' => '']);

        $column->addModal(['icon' => 'cogs'], 'Role Permissions', function (View $v, $id) use ($role) {
            $role->load($id);
            $v->add([Header::class, $role->getTitle() . ' Permissions']);

            $crud = Crud::addTo($v);
            //$crud->setModel($role->ref('AccessRules')); // this way it adds wrong table alias in field condition - looks like ATK bug
            $crud->setModel((new \atk4\login\Model\AccessRule($role->persistence))->addCondition('role_id', $id));
        });

        return parent::setModel($role);
    }
}
