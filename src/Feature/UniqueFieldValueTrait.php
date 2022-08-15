<?php

declare(strict_types=1);

namespace Atk4\Login\Feature;

use Atk4\Data\Model;
use Atk4\Data\ValidationException;

/**
 * Adding this trait to your model will allow it to set fields which should be unique.
 */
trait UniqueFieldValueTrait
{
    /**
     * Set that field value should be unique.
     *
     * @return $this
     */
    public function setUnique(string $fieldName)
    {
        $this->onHook(Model::HOOK_BEFORE_SAVE, function (Model $entity) use ($fieldName) {
            if ($entity->isDirty($fieldName)) {
                $clonedModel = clone $entity->getModel();
                if ($entity->getId() !== null) {
                    $clonedModel->addCondition($entity->idField, '!=', $entity->getId());
                }
                $clonedModel->addCondition($fieldName, $entity->get($fieldName));
                if ($clonedModel->action('exists')->getOne()) {
                    throw new ValidationException([$fieldName => 'Field with such value already exists'], $this);
                }
            }
        });

        return $this;
    }
}
