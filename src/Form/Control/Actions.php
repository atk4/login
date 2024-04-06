<?php

declare(strict_types=1);

namespace Atk4\Login\Form\Control;

class Actions extends GenericDropdown
{
    #[\Override]
    protected function renderView(): void
    {
        $model = $this->getModel();
        if ($model) {
            $actions = array_keys($model->getUserActions());
            $this->values = array_combine($actions, $actions);
        } else {
            $this->values = [];
        }

        parent::renderView();
    }
}
