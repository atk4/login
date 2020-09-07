<?php

declare(strict_types=1);

namespace atk4\login\Form\Control;

/**
 * Form field to choose one or multiple model fields.
 */
class Fields extends Generic
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
