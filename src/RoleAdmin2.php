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
class RoleAdmin2 extends Crud
{
    use DebugTrait;

    /**
     * Initialize Role Admin and add all the UI pieces.
     *
     * @return Model
     */
    public function setModel(Model $role, $fields = null): Model
    {
        // Role executor class
        $roleExecutorClass = get_class(new class() extends \atk4\ui\UserAction\ModalExecutor {
            public function addFormTo(\atk4\ui\View $view): \atk4\ui\Form
            {
                // Role edit form
                $form = parent::addFormTo($view);

                // AccessRules CRUD
                $m_role = $this->action->getOwner();
                $crud = Crud::addTo($view);
                //$m_rules = $m_role->ref('AccessRules'); // this way it adds wrong table alias in field condition - ATK bug (withTitle + table_alias)
                $m_rules = (new \atk4\login\Model\AccessRule($m_role->persistence))->addCondition('role_id', $m_role->getId());

                // Access Rules executor class
                $rulesExecutorClass = get_class(new class() extends \atk4\ui\UserAction\ModalExecutor {
                    protected function jsSetSubmitBtn(View $view, Form $form, string $step)
                    {
                        parent::jsSetSubmitBtn($view, $form, $step);

                        //var_dump($form->getControl('all_visible'));
                        // Somewhere need to set form field visibility rules.
                        // For example, if all_visible is checked, then don't show visible_fields field.
                        // Also put all_visible and visible_fields fields in one group, same for editable fields and actions
                        // Theoreetically (in future) show conditions field probably as ScopeBuilder field.
                        // etc.

                    }
                });
                $m_rules->getUserAction('edit')->ui['executor'] = [$rulesExecutorClass];

                $crud->setModel($m_rules);

                return $form;
            }
        });

        // Add custom user action excutor for edit action
        $role->getUserAction('edit')->ui['executor'] = [$roleExecutorClass];

        return parent::setModel($role, $fields);
    }
}
