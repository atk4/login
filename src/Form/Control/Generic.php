<?php

declare(strict_types=1);

namespace atk4\login\Form\Control;

use atk4\data\Model;
use atk4\ui\Exception;
use atk4\ui\Form\Control\Dropdown;

/**
 * Form field to choose one or multiple entities.
 */
abstract class Generic extends Dropdown
{
    /** @var bool Dropdown with multiselect */
    public $isMultiple = true;

    /**
     * Get AccessRule->model and initialize it.
     *
     * @return Model|null
     */
    public function getModel()
    {
        // prepare values for this dropdown - these will be fields from model of AccessRule->model
        $class = $this->form->model->get('model');
        if (!$class) {
            return;
        }
        if (!class_exists($class)) {
            // ignore if model object can't be created because in some situations model class can be outside of this scope
            //throw new Exception('Can not create model with class name: '.$class);
            return;
        }

        $model = new $class($this->form->model->persistence);
        if (!$model instanceof Model) {
            throw new Exception('Class should be instance of atk4\\data\\Model');
        }

        return $model;
    }
}
