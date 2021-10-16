<?php

declare(strict_types=1);

namespace Atk4\Login\Tests;

use Atk4\Data\Model;
use Atk4\Data\Persistence;
use Atk4\Login\Field\Password;

class PasswordFieldTest extends Generic
{
    public function testPasswordField()
    {
        $m = new Model();
        $m->addField('p', [Password::class]);

        $entity = $m->createEntity();
        $entity->set('p', 'mypass');

        // when setting password, you can retrieve it back while it's not yet saved
        $this->assertSame('mypass', $entity->get('p'));

        // password changed, so it's dirty.
        $this->assertTrue($entity->isDirty('p'));

        $this->assertFalse($entity->compare('p', 'badpass'));
        $this->assertTrue($entity->compare('p', 'mypass'));
    }

    public function testPasswordPersistence()
    {
        $p = new Persistence\Array_();
        $m = new Model($p);

        $m->addField('p', [Password::class]);

        // making sure cloning does not break things
        $entity = $m->createEntity();

        // when setting password, you can retrieve it back while it's not yet saved
        $entity->set('p', 'mypass');
        $this->assertSame('mypass', $entity->get('p'));
        $entity->save();

        // stored encoded password
        $enc = $this->getProtected($p, 'data')['data']->getRowById($m, 1)->getValue('p');
        $this->assertTrue(is_string($enc));
        $this->assertNotSame('mypass', $enc);

        // should have reloaded also
        $this->assertNull($entity->get('p'));

        // password value after load is null, but it still should validate/verify
        $this->assertFalse($entity->getField('p')->verifyPassword('badpass'));
        $this->assertTrue($entity->getField('p')->verifyPassword('mypass'));

        // password shouldn't be dirty here
        $this->assertFalse($entity->isDirty('p'));

        $entity->set('p', 'newpass');
        $this->assertTrue($entity->isDirty('p'));
        $this->assertFalse($entity->getField('p')->verifyPassword('mypass'));
        $this->assertTrue($entity->getField('p')->verifyPassword('newpass'));

        $entity->save();
        $this->assertFalse($entity->isDirty('p'));
        $this->assertFalse($entity->getField('p')->verifyPassword('mypass'));
        $this->assertTrue($entity->getField('p')->verifyPassword('newpass'));

        // will have new hash
        $this->assertNotSame($enc, $this->getProtected($p, 'data')['data']->getRowById($m, 1)->getValue('p'));
    }

    public function testCanNotCompareEmptyException()
    {
        $p = new Persistence\Array_();
        $m = new Model($p);

        $m->addField('p', [Password::class]);

        $this->expectException(\Atk4\Data\Exception::class);
        $m->getField('p')->verifyPassword('mypass'); // tries to compare empty password field value with value 'mypass'
    }

    public function testSuggestPassword()
    {
        $field = new Password();
        $pwd = $field->suggestPassword(6);
        $this->assertIsString($pwd);
        $this->assertGreaterThanOrEqual(6, strlen($pwd));
    }
}
