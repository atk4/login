<?php

declare(strict_types=1);

namespace Atk4\Login\Tests\Feature;

use Atk4\Login\Tests\Generic;

class SignupTest extends Generic
{
    public function testBasic()
    {
        $this->setupDefaultDb();
        $m = $this->createUserModel();

        $this->assertTrue($m->hasUserAction('register_new_user'));

        // as result it makes model loaded (as entity) with new user record
        $m->executeUserAction(
            'register_new_user',
            ['name' => 'New user', 'email' => 'test', 'password' => 'testpass']
        );

        $this->assertSame(1, count((new $m($m->persistence))->addCondition('email', 'test')->export()));
    }
}
