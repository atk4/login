<?php

declare(strict_types=1);

namespace Atk4\Login\Model;

use Atk4\Data\Model;
use Atk4\Login\Feature\SetupModel;
use Atk4\Login\Feature\UniqueFieldValue;

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

        $this->hasMany('Users', ['model' => [User::class], 'our_field' => 'id', 'their_field' => 'role_id']);
        $this->hasMany('AccessRules', ['model' => [AccessRule::class], 'our_field' => 'id', 'their_field' => 'role_id']);

        // traits
        $this->setupRoleModel();
    }
}
