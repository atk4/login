<?php

declare(strict_types=1);

namespace Atk4\Login;

use Atk4\Core\DebugTrait;
use Atk4\Data\Model;
use Atk4\Login\Model\AccessRule;
use Atk4\Ui\Crud;
use Atk4\Ui\Header;
use Atk4\Ui\Table\Column\ActionButtons;
use Atk4\Ui\View;

/**
 * View for Role administration.
 * Includes Role association with AccessRule.
 */
class RoleAdmin extends Crud
{
    use DebugTrait;

    /**
     * Initialize User Admin and add all the UI pieces.
     */
    public function setModel(Model $role, array $fields = null): void
    {
        parent::setModel($role);

        // Add new table column used for actions
        $column = $this->table->addColumn(null, [ActionButtons::class, 'caption' => '']);

        $column->addModal(['icon' => 'cogs'], 'Role Permissions', function (View $v, $id) use ($role) {
            $role = $role->load($id);
            Header::addTo($v, [$role->getTitle() . ' Permissions']);

            $crud = Crud::addTo($v);
            $crud->setModel($role->ref('AccessRules'));

            $crud->onFormAddEdit(function ($f) {
                // @todo - these lines below don't work. One reason is that there is no rule isNotChecked :) but still not sure it works
                $f->setControlsDisplayRules(['visible_fields' => ['all_visible' => 'isNotChecked']]);
                $f->setControlsDisplayRules(['editable_fields' => ['all_editable' => 'isNotChecked']]);
                $f->setControlsDisplayRules(['actions' => ['all_actions' => 'isNotChecked']]);

                // @todo Also it would be good to group all_visible + visible_fields field together in one group/line. Same for editable fields and actions.
            });
        });
    }
}
