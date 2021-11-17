<?php

declare(strict_types=1);

namespace Atk4\Login\Tests;

use Atk4\Data\Model;
use Atk4\Data\Persistence;
use Atk4\Login\Field\Password;
use Atk4\Login\Field\Password as PasswordField;

class PasswordFieldTest extends GenericTestCase
{
    public function testPasswordField(): void
    {
        $m = new Model();
        $m->addField('p', [Password::class]);
        $entity = $m->createEntity();

        // password is immediately hashed, only verify can be used to check if it matches
        $entity->set('p', 'mypass');
        $this->assertNotSame('mypass', $entity->get('p'));
        $this->assertFalse(PasswordField::assertInstanceOf($entity->getField('p'))->verifyPassword('badpass'));
        $this->assertTrue(PasswordField::assertInstanceOf($entity->getField('p'))->verifyPassword('mypass'));

        // setting password to the same value does not rehash
        $v = $entity->get('p');
        $entity->set('p', 'mypass');
        $this->assertSame($v, $entity->get('p'));
    }

    public function testPasswordPersistence(): void
    {
        $db = new Persistence\Array_();

        $m = new Model($db);
        $m->addField('p', [Password::class]);
        $entity = $m->createEntity();

        $entity->set('p', 'mypass');

        // password changed, so it's dirty.
        $this->assertTrue($entity->isDirty('p'));
        $entity->save();
        $this->assertFalse($entity->isDirty('p'));
        $entity->set('p', 'mypass');
        $this->assertFalse($entity->isDirty('p'));
        $entity->set('p', 'mypass2');
        $this->assertTrue($entity->isDirty('p'));
    }

    public function testCanNotCompareEmptyException(): void
    {
        $p = new Persistence\Array_();
        $m = new Model($p);

        $m->addField('p', [Password::class]);

        $this->expectException(\Atk4\Data\Exception::class);
        PasswordField::assertInstanceOf($m->getField('p'))->verifyPassword('mypass'); // tries to compare empty password field value with value 'mypass'
    }

    public function testSuggestPassword(): void
    {
        $field = new Password();
        $pwd = $field->suggestPassword(6);
        $this->assertIsString($pwd);
        $this->assertGreaterThanOrEqual(6, strlen($pwd));
    }
}
