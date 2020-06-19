<?php

declare(strict_types=1);

namespace atk4\login\FormField;

/**
 * Form field to choose one or multiple model fields.
 */
class FieldsDropDown extends AbstractDropDown
{
    public function setModel($model, $fields = null)
    {
        // set function for dropdown row rendering
        $this->renderRowFunction = function ($field) {
            return [
                'value' => $field->short_name,
                'title' => $field->getCaption(),
                //'icon' => ($field->short_name == $field->model->id_field ? 'key' : null), // can not get field->model here :(
            ];
        };

        parent::setModel($model, $fields);
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

        $fields = array_keys($model->getFields());
        $this->values = array_combine($fields, $fields);

        parent::renderView();
    }
}
