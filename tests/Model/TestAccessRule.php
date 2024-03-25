<?php

declare(strict_types=1);

namespace Atk4\Login\Tests\Model;

use Atk4\Login\Model\AccessRule;

class TestAccessRule extends AccessRule
{
    public $table = 'unit_access_rule';
    protected array $roleModelSeed = [TestRole::class];
}
