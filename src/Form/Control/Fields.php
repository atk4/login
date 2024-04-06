<?php

declare(strict_types=1);

namespace Atk4\Login\Form\Control;

class Fields extends GenericDropdown
{
    #[\Override]
    protected function renderView(): void
    {
        $model = $this->getModel();
        if ($model) {
            $fields = array_keys($model->getFields());
            $this->values = array_combine($fields, $fields);
        } else {
            $this->values = [];
        }

        parent::renderView();
    }
}
