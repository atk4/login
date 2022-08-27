<?php

declare(strict_types=1);

namespace Atk4\Login\Form\Control;

use Atk4\Data\Model;

class Actions extends GenericDropdown
{
    public function setModel(Model $model, array $fields = null): void
    {
        $this->renderRowFunction = function ($action) {
            return [
                'value' => $action->shortName,
                'title' => $action->caption ?? $action->shortName,
                'icon' => ($action->ui['icon'] ?? null),
            ];
        };

        parent::setModel($model);
    }

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
