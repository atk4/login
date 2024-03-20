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
        self::assertSame(8, strlen($model->generateRandomPassword()));
        self::assertSame(8, strlen($model->generateRandomPassword(4)));
        self::assertSame(12, strlen($model->generateRandomPassword(12)));
    }

    public function testBasic(): void
    {
        $this->setupDefaultDb();
        $model = $this->createUserModel();

        self::assertTrue($model->hasUserAction('generateRandomPassword'));
        self::assertTrue($model->hasUserAction('resetPassword'));
        self::assertTrue($model->hasUserAction('checkPasswordStrength'));

        // simply generate password and return it
        self::assertIsString($model->createEntity()->executeUserAction('generateRandomPassword', 8));

        // generate new password and set model record password field and save it and email if possible
        $entity = $model->load(1);
        // replace callback so we can catch it
        $entity->getUserAction('sendEmail')->callback = static function () {
            $args = func_get_args();
            static::assertInstanceOf(User::class, $args[0]);
            static::assertStringContainsString('reset', $args[1]);
            static::assertIsString($args[2]);
        };

        self::assertIsString($pass = $entity->executeUserAction('resetPassword', 8));
        self::assertTrue(PasswordField::assertInstanceOf($entity->getField('password'))->verifyPassword($entity, $pass));
        $entity->reload();
        self::assertTrue(PasswordField::assertInstanceOf($entity->getField('password'))->verifyPassword($entity, $pass));

        // check password strength
        self::assertIsString($entity->executeUserAction('checkPasswordStrength', 'qwerty', ['strength' => 3])); // bad
        self::assertNull($entity->executeUserAction('checkPasswordStrength', 'Qwerty312#~%dsQWRDGFfdfh', ['strength' => 3])); // good

        // check password length
        self::assertIsString($entity->executeUserAction('checkPasswordStrength', 'qwerty', ['len' => 8])); // bad
        self::assertNull($entity->executeUserAction('checkPasswordStrength', 'Qwerty312#~%dsQWRDGFfdfh', ['len' => 8])); // good

        // check password symbols
        self::assertIsString($entity->executeUserAction('checkPasswordStrength', 'qwerty', ['symbols' => 4])); // bad
        self::assertNull($entity->executeUserAction('checkPasswordStrength', 'Qwerty312##$$%%^^@@fdsfs', ['symbols' => 4])); // good

        // check password numbers
        self::assertIsString($entity->executeUserAction('checkPasswordStrength', 'qwerty', ['numbers' => 4])); // bad
        self::assertNull($entity->executeUserAction('checkPasswordStrength', 'Qwerty312634dgf#@$', ['numbers' => 4])); // good

        // check password upper letters
        self::assertIsString($entity->executeUserAction('checkPasswordStrength', 'qwerty', ['upper' => 4])); // bad
        self::assertNull($entity->executeUserAction('checkPasswordStrength', 'QwERTYqAZ324', ['upper' => 4])); // good
    }
}
