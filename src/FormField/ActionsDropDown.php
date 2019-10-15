<?php
namespace atk4\login\FormField;

/**
 * Form field to choose one or multiple model actions.
 */
class ActionsDropDown extends AbstractDropDown
{
    public function init()
    {
        parent::init();

        // set function for dropdown row rendering
        $this->renderRowFunction = function ($action) {
            return [
                'value' => $action->short_name,
                'title' => $action->caption ?: $action->short_name,
                'icon' => ($action->ui['icon'] ?? null),
            ];
        };
    }

    /**
     * Renders view.
     */
    public function renderView()
    {
        $model = $this->getModel();
        if (!$model) {
            return parent::renderView();
        }
        $this->values = $model->getActions();

        parent::renderView();
    }
}
