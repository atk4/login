<?php

declare(strict_types=1);

namespace atk4\login\tests;

use atk4\core\AtkPhpunit\TestCase;
use atk4\login\Auth;

class AuthTest extends TestCase
{
    public function testGetCacheKey()
    {
        $auth = new Auth();
        $this->assertSame(
            Auth::class,
            $this->callProtected($auth, 'getCacheKey')
        );

        $auth->name = 'SOMENAME';

        $this->assertSame(
            'SOMENAME',
            $this->callProtected($auth, 'getCacheKey')
        );
    }

    /*
     * currently generates error:
     * session_start(): Cannot start session when headers already sent
     */
    /*public function testGetCachedData()
    {
        $auth = new Auth();
        $res = $this->callProtected($auth, 'getCachedData');
        $this->assertSame(
            [],
            $res
        );
        $this->assertTrue(isset($_SESSION[Auth::class]));
    }*/
}
