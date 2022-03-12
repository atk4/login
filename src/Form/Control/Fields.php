<?php

declare(strict_types=1);

namespace Atk4\Login\Form\Control;

use Atk4\Data\Model;

/**
 * Form field to choose one or multiple model fields.
 */
class Fields extends GenericDropdown
{
    public function setModel(Model $model, array $fields = null): void
    {
        // set function for dropdown row rendering
        $this->renderRowFunction = function ($field) {
            return [
                'value' => $field->short_name,
                'title' => $field->getCaption(),
                // 'icon' => ($field->short_name == $field->model->id_field ? 'key' : null), // can not get field->model here :(
            ];
        };

        parent::setModel($model);
    }

    /**
     * Renders view.
     */
    protected function renderView(): void
    {
        $model = $this->getModel();
        if ($model) {
            $fields = array_keys($model->getFields());
            $this->values = array_combine($fields, $fields);
        }

        parent::renderView();
    }
}
