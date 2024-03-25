<?php

declare(strict_types=1);

namespace Atk4\Login\Tests\Model;

use Atk4\Login\Model\Role;

class TestRole extends Role
{
    public $table = 'unit_role';
    protected array $userModelSeed = [TestUser::class];
    protected array $accessRuleModelSeed = [TestAccessRule::class];
}
