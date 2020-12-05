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
                $a = new static($m->persistence);
                $a->addCondition($a->id_field, '!=', $m->getId());
                $a->tryLoadBy($field, $m->get($field));
                if ($a->loaded()) {
                    throw new ValidationException([$field => ucwords($field) . ' with such value already exists'], $this);
                }
            }
        });

        return $this;
    }
}
