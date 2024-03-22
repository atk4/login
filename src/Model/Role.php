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
    protected array $_userModelSeed = [User::class];

    /** @var array<mixed> Default AccessRule model. */
    protected array $_accessRuleModelSeed = [AccessRule::class];

    #[\Override]
    protected function init(): void
    {
        parent::init();

        $this->addField('name');

        $this->hasMany('Users', [
            'model' => Model::fromSeed($this->_userModelSeed),
            'ourField' => 'id',
            'theirField' => 'role_id',
        ]);
        $this->hasMany('AccessRules', [
            'model' => Model::fromSeed($this->_accessRuleModelSeed),
            'ourField' => 'id',
            'theirField' => 'role_id',
        ]);

        $this->setupRoleModel();
    }
}
