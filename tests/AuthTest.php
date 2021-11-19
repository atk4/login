<?php

declare(strict_types=1);

namespace Atk4\Login\Tests;

use Atk4\Login\Auth;

class AuthTest extends GenericTestCase
{
    public function testDb(): void
    {
        $this->setupDefaultDb();

        $u = $this->createUserModel();
        $this->assertSame(2, count($u->export()));

        $r = $this->createRoleModel();
        $this->assertSame(2, count($r->export()));

        $a = $this->createAccessRuleModel();
        $this->assertSame(3, count($a->export()));

        // password field should not be visible in UI by default
        $this->assertFalse($u->getField('password')->isVisible());

        // test traversal
        $this->assertSame(2, count((clone $u)->load(2)->ref('AccessRules')->export()));
        $this->assertSame(2, (clone $u)->load(2)->ref('role_id')->getId());
        $this->assertSame(2, count((clone $r)->load(2)->ref('AccessRules')->export()));
        $this->assertSame(1, count((clone $r)->load(2)->ref('Users')->export()));
        $this->assertSame(2, (clone $a)->load(2)->ref('role_id')->getId());
    }

    public function _testAuth(bool $cacheEnabled): void
    {
        $this->setupDefaultDb();

        $createAuthFx = function (array $options = []) use ($cacheEnabled) {
            $auth = new Auth(array_merge(['check' => false, 'cacheEnabled' => $cacheEnabled], $options));
            $auth->setModel($this->createUserModel());

            return $auth;
        };

        // test Auth without automatic check to avoid UI involvement (login form, user menu etc)
        $auth = $createAuthFx();
        $this->assertFalse($auth->isLoggedIn());

        // wrong login
        $ok = $auth->tryLogin('admin', 'wrong');
        $this->assertFalse($ok);
        $this->assertFalse($auth->isLoggedIn());

        // correct login
        $ok = $auth->tryLogin('admin', 'admin');
        $this->assertTrue($ok);
        $this->assertTrue($auth->isLoggedIn());

        // logout
        $auth->logout();
        $this->assertFalse($auth->isLoggedIn());

        // now login again, try to change some property, save and check if it's changed in actual DB
        $auth = $createAuthFx();

        $auth->tryLogin('user', 'user');
        $this->assertTrue($auth->isLoggedIn());
        $this->assertSame('user', $auth->user->get($auth->fieldLogin));
        $auth->user->set('name', 'Test User');
        $this->assertSame('Test User', $auth->user->get('name'));
        $auth->user->save();
        $this->assertSame('Test User', $auth->user->get('name'));

        $auth = $createAuthFx();
        $auth->tryLogin('user', 'user');
        $this->assertTrue($auth->isLoggedIn());
        $this->assertSame('user', $auth->user->get($auth->fieldLogin));
        $this->assertSame('Test User', $auth->user->get('name'));

        // now create new Auth object, set model and see if it will pick up
        // last logged user from cache
        if ($cacheEnabled) {
            $auth = $createAuthFx();
            $this->assertTrue($auth->isLoggedIn());
            $this->assertSame('user', $auth->user->get($auth->fieldLogin));
            $this->assertSame('Test User', $auth->user->get('name'));
        }

        if ($cacheEnabled) {
            $createAuthWithShortExpireTimeFx = function () use ($createAuthFx) {
                return $createAuthFx([
                    'cacheOptions' => ['expireTime' => 0.05], // 50 milliseconds
                ]);
            };

            $auth = $createAuthWithShortExpireTimeFx();
            $auth->tryLogin('admin', 'admin'); // saves in cache and set timer

            $auth = $createAuthWithShortExpireTimeFx();
            $this->assertTrue($auth->isLoggedIn());

            // now sleep more than expireTime (cache should expire) and try again
            usleep(60_000);
            $auth = $createAuthWithShortExpireTimeFx();
            $this->assertFalse($auth->isLoggedIn());
        }
    }

    public function testAuth(): void
    {
        $this->_testAuth(false);
    }

    public function testAuthWithCache(): void
    {
        $this->_testAuth(true);
    }

    public function testAuthCustomLoginAndPasswordFieldName(): void
    {
        $this->setupDefaultDb();

        $auth = new Auth(['check' => false]);
        $auth->setModel($this->createUserModel(), 'name', null);
        $auth->tryLogin('admin', 'admin'); // there is no "name" = 'admin'
        $this->assertFalse($auth->isLoggedIn());

        $auth = new Auth(['check' => false]);
        $auth->setModel($this->createUserModel(), 'name', null);
        $auth->tryLogin('Administrator', 'admin');
        $this->assertTrue($auth->isLoggedIn());

        $auth = new Auth(['check' => false]);
        $auth->setModel($this->createUserModel(), null, 'name');
        $this->expectException(\Exception::class);
        $auth->tryLogin('admin', 'admin'); // wrong password field
    }
}
