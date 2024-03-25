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

    /** @var array<mixed> Default User model. */
    protected array $userModelSeed = [User::class];

    /** @var array<mixed> Default AccessRule model. */
    protected array $accessRuleModelSeed = [AccessRule::class];

    #[\Override]
    protected function init(): void
    {
        parent::init();

        $this->addField('name');

        $this->hasMany('Users', [
            'model' => $this->userModelSeed,
            'ourField' => 'id',
            'theirField' => 'role_id',
        ]);
        $this->hasMany('AccessRules', [
            'model' => $this->accessRuleModelSeed,
            'ourField' => 'id',
            'theirField' => 'role_id',
        ]);

        $this->setupRoleModel();
    }
}
