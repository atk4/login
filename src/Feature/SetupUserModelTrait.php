<?php

declare(strict_types=1);

namespace Atk4\Login\Feature;

use Atk4\Data\Persistence;

trait SetupUserModelTrait
{
    use UniqueFieldValueTrait;

    public function setupUserModel(): void
    {
        $this->getField('name')->required = true;
        $this->getField('email')->required = true;
        $this->setUnique('email');
        $this->getField('password')->required = true;
        $this->getField('password')->ui['visible'] = false;

        // all AccessRules for all user roles
        // @TODO in future when there can be multiple, then merge them together
        $this->hasMany('AccessRules', [
            'model' => function (Persistence $p, array $defaults = []) {
                return $this->ref('role_id')->ref('AccessRules');
            },
            'ourField' => 'role_id',
            'theirField' => 'role_id',
        ]);
    }
}
