<?php

declare(strict_types=1);

namespace Atk4\Login\Feature;

use Atk4\Data\Model;
use Atk4\Data\ValidationException;

/**
 * Adding this trait to your model will allow it to set fields which should be unique.
 */
trait UniqueFieldValue
{
    /**
     * Set that field value should be unique.
     *
     * @return $this
     */
    public function setUnique(string $field)
    {
        $this->onHook(Model::HOOK_BEFORE_SAVE, function ($m) use ($field) {
            if ($m->isDirty($field)) {
                $model = new static($m->persistence);
                $model->addCondition($model->id_field, '!=', $m->getId());
                $entity = $model->tryLoadBy($field, $m->get($field));
                if ($entity->loaded()) {
                    throw new ValidationException([$field => ucwords($field) . ' with such value already exists'], $this);
                }
            }
        });

        return $this;
    }
}
