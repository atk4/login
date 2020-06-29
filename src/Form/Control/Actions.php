<?php

declare(strict_types=1);

namespace atk4\login\Form\Control;

/**
 * Form field to choose one or multiple model actions.
 */
class Actions extends Generic
{
    public function setModel($model, $fields = null)
    {
        // set function for dropdown row rendering
        $this->renderRowFunction = function ($action) {
            return [
                'value' => $action->short_name,
                'title' => $action->caption ?: $action->short_name,
                'icon' => ($action->ui['icon'] ?? null),
            ];
        };

        return parent::setModel($model, $fields);
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

        $actions = array_keys($model->getActions());
        $this->values = array_combine($actions, $actions);

        parent::renderView();
    }
}
