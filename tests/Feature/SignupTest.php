<?php

declare(strict_types=1);

namespace Atk4\Login\Tests\Feature;

use Atk4\Data\Field\PasswordField;
use Atk4\Login\Tests\GenericTestCase;

class SignupTest extends GenericTestCase
{
    public function testBasic(): void
    {
        $this->setupDefaultDb();
        $m = $this->createUserModel();

        self::assertTrue($m->hasUserAction('registerNewUser'));

        // as result it makes model loaded (as entity) with new user record
        $m->createEntity()->executeUserAction('registerNewUser', [
            'name' => 'New user',
            'email' => 'test',
            'password' => PasswordField::assertInstanceOf($m->getField('password'))->hashPassword('testpass'),
        ]);

        self::assertCount(1, (new $m($m->getPersistence()))->addCondition('email', 'test')->export());
    }
}
