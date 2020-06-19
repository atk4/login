<?php

declare(strict_types=1);

namespace atk4\data\tests;

use atk4\data\Model;
use atk4\data\Persistence;
use atk4\login\Field\Password;

class PasswordTest extends \atk4\core\AtkPhpunit\TestCase
{
    public function testPasswordField()
    {
        $m = new Model(); //$db, 'job');

        $m->addField('p', ['\atk4\login\Field\Password']);

        $m->set('p', 'mypass');

        // when setting password, you cannot retrieve it back
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

        # making sure cloning does not break things
        $m = clone $m;

        $m->set('p', 'mypass');
        $this->assertSame('mypass', $m->get('p'));
        $m->save();

        $reflection = new \ReflectionClass($p);
        $reflection_property = $reflection->getProperty('data');
        $reflection_property->setAccessible(true);

        //var_dump($reflection_property->getValue($p)['data']);
        $enc = $reflection_property->getValue($p)['data'][1]['p']; // stored encoded password
        $this->assertTrue(is_string($enc));
        $this->assertNotSame('mypass', $enc);

        // should have reloaded also
        $this->assertNull($m->get('p'));

        $this->assertFalse($m->compare('p', 'badpass'));
        $this->assertTrue($m->compare('p', 'mypass'));

        // password shouldn't be dirty here
        $this->assertFalse($m->isDirty('p'));

        $m->set('p', 'newpass');

        $this->assertTrue($m->isDirty('p'));
        $this->assertFalse($m->compare('p', 'mypass'));
        $this->assertTrue($m->compare('p', 'newpass'));

        $m->save();

        // will have new hash
        $this->assertNotSame($enc, $reflection_property->getValue($p)['data'][1]['p']);
    }

    public function testCanNotCompareEmptyException1()
    {
        $this->expectException(\atk4\data\Exception::class);
        $a = [];
        $p = new Persistence\Array_($a);
        $m = new Model($p);

        $m->addField('p', [Password::class]);
        $m->compare('p', 'mypass'); // tries to compare empty password field value with value 'mypass'
    }

    public function testPasswordCompareException2()
    {
        $this->expectException(\atk4\data\Exception::class);

        $a = [];
        $p = new Persistence\Array_($a);
        $m = new Model($p);

        $m->addField('p', ['\atk4\login\Field\Password']);
        $m->compare('p', 'mypass');
    }
}
