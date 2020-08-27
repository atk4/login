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
        /** @var \atk4\ui\Table\Column $column */
        $column = $this->crud->table->addColumn(null, [ActionButtons::class, 'caption' => '']);

        $column->addModal(['icon' => 'cogs'], 'Role Permissions', function (View $v, $id) {
            $this->model->load($id);

            $v->add([Header::class, $this->model->getTitle() . ' Permissions']);

            /** @var Crud $crud */
            $crud = Crud::addTo($v);
            $crud->setModel($this->model->ref('AccessRules'));
        });

        //@todo remove this line. It's just a workaround while CRUD edit action button will be fixed in modal windows
        $this->crud->owner->add([Crud::class])->setModel($this->crud->model->ref('AccessRules'));

        return parent::setModel($role);
    }
}
