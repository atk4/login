<?php

declare(strict_types=1);

namespace atk4\login\Feature;

use atk4\data\ValidationException;

/**
 * Adding this trait to your model will allow it to set fields which should be unique.
 */
trait UniqueFieldValue
{
    /**
     * Set that field value should be unique.
     *
     * @param string $field
     *
     * @return $this
     */
    public function setUnique($field)
    {
        $this->onHook('beforeSave', function ($m) use ($field) {
            if ($m->isDirty($field)) {
                $a = new static($m->persistence);
                $a->addCondition($m->id_field, '<>', $m->id);
                $a->tryLoadBy($field, $m->get($field));
                if ($a->loaded()) {
                    throw new ValidationException([$field => ucwords($field) . ' with such value already exists'], $this);
                }
            }
        });

        return $this;
    }
}
