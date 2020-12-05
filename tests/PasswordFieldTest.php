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

        $m->set('p', 'mypass');

        // when setting password, you can retrieve it back while it's not yet saved
        $this->assertSame('mypass', $m->get('p'));

        // password changed, so it's dirty.
        $this->assertTrue($m->isDirty('p'));

        $this->assertFalse($m->compare('p', 'badpass'));
        $this->assertTrue($m->compare('p', 'mypass'));
    }

    public function testPasswordPersistence()
    {
        $a = [];
        $p = new Persistence\Array_($a);
        $m = new Model($p);

        $m->addField('p', [Password::class]);

        // making sure cloning does not break things
        $m = clone $m;

        // when setting password, you can retrieve it back while it's not yet saved
        $m->set('p', 'mypass');
        $this->assertSame('mypass', $m->get('p'));
        $m->save();

        // stored encoded password
        $enc = $this->getProtected($p, 'data')['data'][1]['p'];
        $this->assertTrue(is_string($enc));
        $this->assertNotSame('mypass', $enc);

        // should have reloaded also
        $this->assertNull($m->get('p'));

        // password value after load is null, but it still should validate/verify
        $this->assertFalse($m->getField('p')->verify('badpass'));
        $this->assertTrue($m->getField('p')->verify('mypass'));

        // password shouldn't be dirty here
        $this->assertFalse($m->isDirty('p'));

        $m->set('p', 'newpass');
        $this->assertTrue($m->isDirty('p'));
        $this->assertFalse($m->getField('p')->verify('mypass'));
        $this->assertTrue($m->getField('p')->verify('newpass'));

        $m->save();
        $this->assertFalse($m->isDirty('p'));
        $this->assertFalse($m->getField('p')->verify('mypass'));
        $this->assertTrue($m->getField('p')->verify('newpass'));

        // will have new hash
        $this->assertNotSame($enc, $this->getProtected($p, 'data')['data'][1]['p']);
    }

    public function testCanNotCompareEmptyException()
    {
        $a = [];
        $p = new Persistence\Array_($a);
        $m = new Model($p);

        $m->addField('p', [Password::class]);

        $this->expectException(\Atk4\Data\Exception::class);
        $m->getField('p')->verify('mypass'); // tries to compare empty password field value with value 'mypass'
    }

    public function testSuggestPassword()
    {
        $field = new Password();
        $pwd = $field->suggestPassword(6);
        $this->assertIsString($pwd);
        $this->assertGreaterThanOrEqual(6, strlen($pwd));
    }
}
