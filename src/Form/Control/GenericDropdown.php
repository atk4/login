<?php

declare(strict_types=1);

namespace Atk4\Login\Form\Control;

use Atk4\Data\Model;
use Atk4\Ui\Exception;
use Atk4\Ui\Form;

/**
 * Form field to choose one or multiple entities.
 */
abstract class GenericDropdown extends Form\Control\Dropdown
{
    /** @var bool Dropdown with multiselect */
    public $multiple = true;

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
            return null;
        }
        if (!class_exists($class)) {
            // ignore if model class does not exist, in some situations it can be unavailable or be an interface
            return null;
        }

        $model = new $class($this->form->model->getModel()->getPersistence());
        if (!$model instanceof Model) {
            throw new Exception('Class must be instance of ' . Model::class);
        }

        return $model;
    }
}
