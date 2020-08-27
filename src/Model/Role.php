<?php

declare(strict_types=1);

namespace atk4\login\Model;

use atk4\data\Model;
use atk4\login\Feature\SetupModel;
use atk4\login\Feature\UniqueFieldValue;

class Role extends Model
{
    use SetupModel;
    use UniqueFieldValue;

    public $table = 'login_role';
    public $caption = 'Role';

    protected function init(): void
    {
        parent::init();

        $this->addField('name');

        $this->hasMany('Users', [User::class, 'our_field' => 'id', 'their_field' => 'role_id']);
        $this->hasMany('AccessRules', [AccessRule::class, 'our_field' => 'id', 'their_field' => 'role_id']);

        // traits
        $this->setupRoleModel();
    }
}
