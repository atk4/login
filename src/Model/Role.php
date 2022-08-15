<?php

declare(strict_types=1);

namespace Atk4\Login\Model;

use Atk4\Data\Model;
use Atk4\Login\Feature\SetupRoleModelTrait;

class Role extends Model
{
    use SetupRoleModelTrait;

    public $table = 'login_role';
    public $caption = 'Role';

    protected function init(): void
    {
        parent::init();

        $this->addField('name');

        $this->hasMany('Users', ['model' => [User::class], 'ourField' => 'id', 'theirField' => 'role_id']);
        $this->hasMany('AccessRules', ['model' => [AccessRule::class], 'ourField' => 'id', 'theirField' => 'role_id']);

        $this->setupRoleModel();
    }
}
