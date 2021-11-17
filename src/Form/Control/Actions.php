<?php

declare(strict_types=1);

namespace Atk4\Login\Form\Control;

use Atk4\Data\Model;

/**
 * Form field to choose one or multiple model actions.
 */
class Actions extends Generic
{
    public function setModel(Model $model, array $fields = null): void
    {
        // set function for dropdown row rendering
        $this->renderRowFunction = function ($action) {
            return [
                'value' => $action->short_name,
                'title' => $action->caption ?: $action->short_name,
                'icon' => ($action->ui['icon'] ?? null),
            ];
        };

        parent::setModel($model, $fields);
    }

    /**
     * Renders view.
     */
    protected function renderView(): void
    {
        $model = $this->getModel();
        if ($model) {
            $actions = array_keys($model->getUserActions());
            $this->values = array_combine($actions, $actions);
        }

        parent::renderView();
    }
}
