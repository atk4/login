<?php

declare(strict_types=1);

namespace Atk4\Login\Form\Control;

use Atk4\Data\Field;
use Atk4\Data\Model;

class Actions extends GenericDropdown
{
    #[\Override]
    public function setModel(Model $model): void
    {
        $this->renderRowFunction = static function (Field $field) {
            return [
                'value' => $field->shortName,
                'title' => $field->caption ?? $field->shortName,
                'icon' => $field->ui['icon'] ?? null,
            ];
        };

        parent::setModel($model);
    }

    #[\Override]
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
