<?php

declare(strict_types=1);

namespace atk4\ui\tests;

class BasicTest extends \atk4\core\AtkPhpunit\TestCase
{
    /**
     * Test constructor.
     */
    public function testTesting()
    {
        $this->assertSame('foo', 'foo');
    }
}
