<?php
namespace atk4\login\FormField;

use atk4\data\Model;
use atk4\ui\Exception;
use atk4\ui\FormField\DropDown;

/**
 * Form field to choose one or multiple entities.
 */
abstract class AbstractDropDown extends DropDown
{
    /** @var bool Dropdown with multiselect */
    public $isMultiple = true;

    /**
     * Get AccessRule->model and initialize it
     *
     * @return Model
     */
    public function getModel()
    {
        // prepare values for this dropdown - these will be fields from model of AccessRule->model
        $class = $this->form->model['model'];
        if (!$class) {
            return;
        }
        if (!class_exists($class)) {
            throw new Exception('Can not create model with class name: '.$class);
        }

        $model = new $class($this->form->model->persistence);
        if (!$model instanceof Model) {
            throw new Exception('Class should be instance of atk4\\data\\Model');
        }

        return $model;
    }
}
