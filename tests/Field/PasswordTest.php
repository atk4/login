<?php

declare(strict_types=1);

namespace atk4\login\tests\Field;

use atk4\data\Model;
use atk4\data\Persistence;
use atk4\login\Field\Password;

class PasswordTest extends \atk4\core\AtkPhpunit\TestCase
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

    public function testPasswordPersistence1()
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

        $enc = $this->getProtected($p, 'data')['data'][1]['p']; // stored encoded password

        $this->assertTrue(is_string($enc));
        $this->assertNotSame('mypass', $enc);

        // should have reloaded also
        $this->assertNull($m->get('p'));

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

    public function testCanNotCompareEmptyException1()
    {
        $this->expectException(\atk4\data\Exception::class);
        $a = [];
        $p = new Persistence\Array_($a);
        $m = new Model($p);

        $m->addField('p', [Password::class]);
        $m->getField('p')->verify('mypass'); // tries to compare empty password field value with value 'mypass'
    }

    public function testPasswordCompareException2()
    {
        $this->expectException(\atk4\data\Exception::class);

        $a = [];
        $p = new Persistence\Array_($a);
        $m = new Model($p);

        $m->addField('p', [Password::class]);
        $m->getField('p')->verify('mypass');
    }

    public function testSuggestPassword()
    {
        $field = new Password();
        $pwd = $field->suggestPassword(6);
        $this->assertIsString($pwd);
        $this->assertGreaterThanOrEqual(6, strlen($pwd));
    }
}
