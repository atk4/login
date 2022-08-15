<?php

declare(strict_types=1);

namespace Atk4\Login\Tests\Feature;

use Atk4\Data\Field\PasswordField;
use Atk4\Data\Model;
use Atk4\Data\Persistence;
use Atk4\Login\Feature\PasswordManagementTrait;
use Atk4\Login\Model\User;
use Atk4\Login\Tests\GenericTestCase;

class PasswordManagementTest extends GenericTestCase
{
    public function testGenerateRandomPassword(): void
    {
        $class = new class() extends Model {
            use PasswordManagementTrait;
        };
        $model = new $class(new Persistence\Array_());
        $this->assertIsString($model->generateRandomPassword(4));
    }

    public function testBasic(): void
    {
        $this->setupDefaultDb();
        $model = $this->createUserModel();

        $this->assertTrue($model->hasUserAction('generateRandomPassword'));
        $this->assertTrue($model->hasUserAction('resetPassword'));
        $this->assertTrue($model->hasUserAction('checkPasswordStrength'));

        // simply generate password and return it
        $this->assertIsString($model->createEntity()->executeUserAction('generateRandomPassword', 8));

        // generate new password and set model record password field and save it and email if possible
        $entity = $model->load(1);
        // replace callback so we can catch it
        $entity->getUserAction('sendEmail')->callback = function () {
            $args = func_get_args();
            $this->assertInstanceOf(User::class, $args[0]);
            $this->assertStringContainsString('reset', $args[1]);
            $this->assertIsString($args[2]);
        };

        $this->assertIsString($pass = $entity->executeUserAction('resetPassword', 8));
        $this->assertTrue(PasswordField::assertInstanceOf($entity->getField('password'))->verifyPassword($entity, $pass));
        $entity->reload();
        $this->assertTrue(PasswordField::assertInstanceOf($entity->getField('password'))->verifyPassword($entity, $pass));

        // check password strength
        $this->assertIsString($entity->executeUserAction('checkPasswordStrength', 'qwerty', ['strength' => 3])); // bad
        $this->assertNull($entity->executeUserAction('checkPasswordStrength', 'Qwerty312#~%dsQWRDGFfdfh', ['strength' => 3])); // good

        // check password length
        $this->assertIsString($entity->executeUserAction('checkPasswordStrength', 'qwerty', ['len' => 8])); // bad
        $this->assertNull($entity->executeUserAction('checkPasswordStrength', 'Qwerty312#~%dsQWRDGFfdfh', ['len' => 8])); // good

        // check password symbols
        $this->assertIsString($entity->executeUserAction('checkPasswordStrength', 'qwerty', ['symbols' => 4])); // bad
        $this->assertNull($entity->executeUserAction('checkPasswordStrength', 'Qwerty312##$$%%^^@@fdsfs', ['symbols' => 4])); // good

        // check password numbers
        $this->assertIsString($entity->executeUserAction('checkPasswordStrength', 'qwerty', ['numbers' => 4])); // bad
        $this->assertNull($entity->executeUserAction('checkPasswordStrength', 'Qwerty312634dgf#@$', ['numbers' => 4])); // good

        // check password upper letters
        $this->assertIsString($entity->executeUserAction('checkPasswordStrength', 'qwerty', ['upper' => 4])); // bad
        $this->assertNull($entity->executeUserAction('checkPasswordStrength', 'QwERTYqAZ324', ['upper' => 4])); // good
    }
}
