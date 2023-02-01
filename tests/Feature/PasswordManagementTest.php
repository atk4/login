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
        static::assertSame(4, strlen($model->generateRandomPassword(4)));
    }

    public function testBasic(): void
    {
        $this->setupDefaultDb();
        $model = $this->createUserModel();

        static::assertTrue($model->hasUserAction('generateRandomPassword'));
        static::assertTrue($model->hasUserAction('resetPassword'));
        static::assertTrue($model->hasUserAction('checkPasswordStrength'));

        // simply generate password and return it
        static::assertIsString($model->createEntity()->executeUserAction('generateRandomPassword', 8));

        // generate new password and set model record password field and save it and email if possible
        $entity = $model->load(1);
        // replace callback so we can catch it
        $entity->getUserAction('sendEmail')->callback = function () {
            $args = func_get_args();
            static::assertInstanceOf(User::class, $args[0]);
            static::assertStringContainsString('reset', $args[1]);
            static::assertIsString($args[2]);
        };

        static::assertIsString($pass = $entity->executeUserAction('resetPassword', 8));
        static::assertTrue(PasswordField::assertInstanceOf($entity->getField('password'))->verifyPassword($entity, $pass));
        $entity->reload();
        static::assertTrue(PasswordField::assertInstanceOf($entity->getField('password'))->verifyPassword($entity, $pass));

        // check password strength
        static::assertIsString($entity->executeUserAction('checkPasswordStrength', 'qwerty', ['strength' => 3])); // bad
        static::assertNull($entity->executeUserAction('checkPasswordStrength', 'Qwerty312#~%dsQWRDGFfdfh', ['strength' => 3])); // good

        // check password length
        static::assertIsString($entity->executeUserAction('checkPasswordStrength', 'qwerty', ['len' => 8])); // bad
        static::assertNull($entity->executeUserAction('checkPasswordStrength', 'Qwerty312#~%dsQWRDGFfdfh', ['len' => 8])); // good

        // check password symbols
        static::assertIsString($entity->executeUserAction('checkPasswordStrength', 'qwerty', ['symbols' => 4])); // bad
        static::assertNull($entity->executeUserAction('checkPasswordStrength', 'Qwerty312##$$%%^^@@fdsfs', ['symbols' => 4])); // good

        // check password numbers
        static::assertIsString($entity->executeUserAction('checkPasswordStrength', 'qwerty', ['numbers' => 4])); // bad
        static::assertNull($entity->executeUserAction('checkPasswordStrength', 'Qwerty312634dgf#@$', ['numbers' => 4])); // good

        // check password upper letters
        static::assertIsString($entity->executeUserAction('checkPasswordStrength', 'qwerty', ['upper' => 4])); // bad
        static::assertNull($entity->executeUserAction('checkPasswordStrength', 'QwERTYqAZ324', ['upper' => 4])); // good
    }
}
