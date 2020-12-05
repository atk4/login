<?php

declare(strict_types=1);

namespace Atk4\Login\Tests\Feature;

use Atk4\Login\Tests\Generic;

class SignupTest extends Generic
{
    public function testBasic()
    {
        $this->setupDefaultDb();
        $m = $this->getUserModel();

        $this->assertTrue($m->hasUserAction('register_new_user'));

        $m->executeUserAction(
            'register_new_user',
            ['name' => 'New user', 'email' => 'test', 'password' => 'testpass']
        );
        $this->assertSame(1, count((clone $m)->addCondition('email', 'test')->export()));
    }
}
