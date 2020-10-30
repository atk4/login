<?php

declare(strict_types=1);

namespace atk4\login\tests\Feature;

use atk4\data\Model;
use atk4\login\Feature\Signup;
use atk4\login\Model\User;
use atk4\login\tests\Generic;

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
        $this->assertEquals(1, count((clone $m)->addCondition('email','test')->export()));
    }
}
