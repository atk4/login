<?php

declare(strict_types=1);

namespace Atk4\Login\Tests\Model;

use Atk4\Login\Model\User;

class TestUser extends User
{
    public $table = 'unit_user';
    protected array $roleModelSeed = [TestRole::class];

    #[\Override]
    protected function init(): void
    {
        parent::init();

        $this->addField('last_login', ['type' => 'datetime']);
    }
}
