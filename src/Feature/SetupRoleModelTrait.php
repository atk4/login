<?php

declare(strict_types=1);

namespace Atk4\Login\Feature;

trait SetupRoleModelTrait
{
    use UniqueFieldValueTrait;

    public function setupRoleModel(): void
    {
        $this->getField('name')->required = true;
        $this->setUnique('name');
    }
}
