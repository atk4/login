<?php

declare(strict_types=1);

namespace Atk4\Login\Tests\Feature;

use Atk4\Data\Model;
use Atk4\Data\Persistence;
use Atk4\Login\Feature\PasswordManagementTrait;
use Atk4\Login\Model\User;
use Atk4\Login\Tests\Generic;

class PasswordManagementTest extends Generic
{
    public function testGenerateRandomPassword()
    {
        $class = new class() extends Model {
            use PasswordManagementTrait;
        };
        $model = new $class(new Persistence\Array_());
        $this->assertIsString($model->generate_random_password(4));
    }

    public function testBasic()
    {
        $this->setupDefaultDb();
        $model = $this->createUserModel();

        $this->assertTrue($model->hasUserAction('generate_random_password'));
        $this->assertTrue($model->hasUserAction('reset_password'));
        $this->assertTrue($model->hasUserAction('check_password_strength'));

        // simply generate password and return it
        $this->assertIsString($model->executeUserAction('generate_random_password', 4));

        // generate new password and set model record password field and save it and email if possible
        $entity = $model->load(1);
        // replace callback so we can catch it
        $entity->getUserAction('sendEmail')->callback = function () {
            $args = func_get_args();
            $this->assertInstanceOf(User::class, $args[0]);
            $this->assertStringContainsString('reset', $args[1]);
            $this->assertIsString($args[2]);
        };

        $this->assertIsString($pass = $entity->executeUserAction('reset_password', 4));
        $this->assertTrue($entity->getField('password')->verifyPassword($pass));
        $entity->reload();
        $this->assertTrue($entity->getField('password')->verifyPassword($pass));

        // check password strength
        $this->assertIsString($entity->executeUserAction('check_password_strength', 'qwerty', ['strength' => 3])); // bad
        $this->assertNull($entity->executeUserAction('check_password_strength', 'Qwerty312#~%dsQWRDGFfdfh', ['strength' => 3])); // good

        // check password length
        $this->assertIsString($entity->executeUserAction('check_password_strength', 'qwerty', ['len' => 8])); // bad
        $this->assertNull($entity->executeUserAction('check_password_strength', 'Qwerty312#~%dsQWRDGFfdfh', ['len' => 8])); // good

        // check password symbols
        $this->assertIsString($entity->executeUserAction('check_password_strength', 'qwerty', ['symbols' => 4])); // bad
        $this->assertNull($entity->executeUserAction('check_password_strength', 'Qwerty312##$$%%^^@@fdsfs', ['symbols' => 4])); // good

        // check password numbers
        $this->assertIsString($entity->executeUserAction('check_password_strength', 'qwerty', ['numbers' => 4])); // bad
        $this->assertNull($entity->executeUserAction('check_password_strength', 'Qwerty312634dgf#@$', ['numbers' => 4])); // good

        // check password upper letters
        $this->assertIsString($entity->executeUserAction('check_password_strength', 'qwerty', ['upper' => 4])); // bad
        $this->assertNull($entity->executeUserAction('check_password_strength', 'QwERTYqAZ324', ['upper' => 4])); // good
    }
}
