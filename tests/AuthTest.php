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
        static::assertCount(2, $u->export());

        $r = $this->createRoleModel();
        static::assertCount(2, $r->export());

        $a = $this->createAccessRuleModel();
        static::assertCount(3, $a->export());

        // password field should not be visible in UI by default
        static::assertFalse($u->getField('password')->isVisible());

        // test traversal
        static::assertCount(2, (clone $u)->load(2)->ref('AccessRules')->export());
        static::assertSame(2, (clone $u)->load(2)->ref('role_id')->getId());
        static::assertCount(2, (clone $r)->load(2)->ref('AccessRules')->export());
        static::assertCount(1, (clone $r)->load(2)->ref('Users')->export());
        static::assertSame(2, (clone $a)->load(2)->ref('role_id')->getId());
    }

    public function _testAuth(bool $cacheEnabled): void
    {
        $this->setupDefaultDb();

        $createAuthFx = function (array $options = []) use ($cacheEnabled) {
            $auth = new Auth(
                $this->createAppForSession(),
                array_merge(['check' => false, 'cacheEnabled' => $cacheEnabled], $options)
            );
            $auth->setModel($this->createUserModel());

            return $auth;
        };

        // test Auth without automatic check to avoid UI involvement (login form, user menu etc)
        $auth = $createAuthFx();
        static::assertFalse($auth->isLoggedIn());

        // wrong login
        $ok = $auth->tryLogin('admin', 'wrong');
        static::assertFalse($ok);
        static::assertFalse($auth->isLoggedIn());

        // correct login
        $ok = $auth->tryLogin('admin', 'admin');
        static::assertTrue($ok);
        static::assertTrue($auth->isLoggedIn());

        // logout
        $auth->logout();
        static::assertFalse($auth->isLoggedIn());

        // now login again, try to change some property, save and check if it's changed in actual DB
        $auth = $createAuthFx();

        $auth->tryLogin('user', 'user');
        static::assertTrue($auth->isLoggedIn());
        static::assertSame('user', $auth->user->get($auth->fieldLogin));
        $auth->user->set('name', 'Test User');
        static::assertSame('Test User', $auth->user->get('name'));
        $auth->user->save();
        static::assertSame('Test User', $auth->user->get('name'));

        $auth = $createAuthFx();
        $auth->tryLogin('user', 'user');
        static::assertTrue($auth->isLoggedIn());
        static::assertSame('user', $auth->user->get($auth->fieldLogin));
        static::assertSame('Test User', $auth->user->get('name'));

        // now create new Auth object, set model and see if it will pick up
        // last logged user from cache
        if ($cacheEnabled) {
            $auth = $createAuthFx();
            static::assertTrue($auth->isLoggedIn());
            static::assertSame('user', $auth->user->get($auth->fieldLogin));
            static::assertSame('Test User', $auth->user->get('name'));

            $createAuthWithShortExpireTimeFx = function () use ($createAuthFx) {
                return $createAuthFx([
                    'cacheOptions' => ['expireTime' => 0.05], // 50 milliseconds
                ]);
            };

            $auth = $createAuthWithShortExpireTimeFx();
            $auth->tryLogin('admin', 'admin'); // saves in cache and set timer

            $auth = $createAuthWithShortExpireTimeFx();
            static::assertTrue($auth->isLoggedIn());

            // now sleep more than expireTime (cache should expire) and try again
            usleep(60_000);
            $auth = $createAuthWithShortExpireTimeFx();
            static::assertFalse($auth->isLoggedIn());
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

        $auth = new Auth($this->createAppForSession(), ['check' => false]);
        $auth->setModel($this->createUserModel(), 'name', null);
        $auth->tryLogin('admin', 'admin'); // there is no "name" = 'admin'
        static::assertFalse($auth->isLoggedIn());

        $auth = new Auth($this->createAppForSession(), ['check' => false]);
        $auth->setModel($this->createUserModel(), 'name', null);
        $auth->tryLogin('Administrator', 'admin');
        static::assertTrue($auth->isLoggedIn());

        $auth = new Auth($this->createAppForSession(), ['check' => false]);
        $auth->setModel($this->createUserModel(), null, 'name');

        $this->expectException(\Exception::class);
        $auth->tryLogin('admin', 'admin'); // wrong password field
    }
}
