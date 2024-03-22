<?php

declare(strict_types=1);

namespace Atk4\Login\Tests;

use Atk4\Login\Auth;
use Atk4\Login\Tests\Model\TestUser as User;

class AuthTest extends GenericTestCase
{
    public function testDb(): void
    {
        $this->setupDefaultDb();

        $u = $this->createUserModel();
        self::assertCount(2, $u->export());

        $r = $this->createRoleModel();
        self::assertCount(2, $r->export());

        $a = $this->createAccessRuleModel();
        self::assertCount(3, $a->export());

        // password field should not be visible in UI by default
        self::assertFalse($u->getField('password')->isVisible());

        // test traversal
        self::assertCount(2, (clone $u)->load(2)->ref('AccessRules')->export());
        self::assertSame(2, (clone $u)->load(2)->ref('role_id')->getId());
        self::assertCount(2, (clone $r)->load(2)->ref('AccessRules')->export());
        self::assertCount(1, (clone $r)->load(2)->ref('Users')->export());
        self::assertSame(2, (clone $a)->load(2)->ref('role_id')->getId());
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
        self::assertFalse($auth->isLoggedIn());

        // wrong login
        $ok = $auth->tryLogin('admin', 'wrong');
        self::assertFalse($ok);
        self::assertFalse($auth->isLoggedIn());

        // correct login
        $ok = $auth->tryLogin('admin', 'admin');
        self::assertTrue($ok);
        self::assertTrue($auth->isLoggedIn());

        // logout
        $auth->logout();
        self::assertFalse($auth->isLoggedIn());

        // now login again, try to change some property, save and check if it's changed in actual DB
        $auth = $createAuthFx();

        $auth->tryLogin('user', 'user');
        self::assertTrue($auth->isLoggedIn());
        self::assertSame('user', $auth->user->get($auth->fieldLogin));
        $auth->user->set('name', 'Test User');
        self::assertSame('Test User', $auth->user->get('name'));
        $auth->user->save();
        self::assertSame('Test User', $auth->user->get('name'));

        $auth = $createAuthFx();
        $auth->tryLogin('user', 'user');
        self::assertTrue($auth->isLoggedIn());
        self::assertSame('user', $auth->user->get($auth->fieldLogin));
        self::assertSame('Test User', $auth->user->get('name'));

        // now create new Auth object, set model and see if it will pick up
        // last logged user from cache
        if ($cacheEnabled) {
            $auth = $createAuthFx();
            self::assertTrue($auth->isLoggedIn());
            self::assertSame('user', $auth->user->get($auth->fieldLogin));
            self::assertSame('Test User', $auth->user->get('name'));

            $createAuthWithShortExpireTimeFx = static function () use ($createAuthFx) {
                return $createAuthFx([
                    'cacheOptions' => ['expireTime' => 0.05], // 50 milliseconds
                ]);
            };

            $auth = $createAuthWithShortExpireTimeFx();
            $auth->tryLogin('admin', 'admin'); // saves in cache and set timer

            $auth = $createAuthWithShortExpireTimeFx();
            self::assertTrue($auth->isLoggedIn());

            // now sleep more than expireTime (cache should expire) and try again
            usleep(60_000);
            $auth = $createAuthWithShortExpireTimeFx();
            self::assertFalse($auth->isLoggedIn());
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
        self::assertFalse($auth->isLoggedIn());

        $auth = new Auth($this->createAppForSession(), ['check' => false]);
        $auth->setModel($this->createUserModel(), 'name', null);
        $auth->tryLogin('Administrator', 'admin');
        self::assertTrue($auth->isLoggedIn());

        $auth = new Auth($this->createAppForSession(), ['check' => false]);
        $auth->setModel($this->createUserModel(), null, 'name');

        $this->expectException(\Exception::class);
        $auth->tryLogin('admin', 'admin'); // wrong password field
    }

    public function testCustomUserModel(): void
    {
        $this->setupDefaultDb();

        $auth = new Auth($this->createAppForSession(), ['check' => false]);
        $auth->onHook(Auth::HOOK_LOGGED_IN, static function (Auth $self, User $m) {
            $m->save(['last_login' => new \DateTime()]);
        });

        $auth->setModel($this->createUserModel());
        $auth->tryLogin('admin', 'admin');
        self::assertTrue($auth->isLoggedIn());

        // last login time is set
        $t1 = $auth->user->get('last_login');
        self::assertInstanceOf('DateTime', $t1);

        // sleep a bit and try again, time changes
        usleep(60_000);
        $auth->tryLogin('admin', 'admin');
        $t2 = $auth->user->get('last_login');
        self::assertInstanceOf('DateTime', $t2);
        self::assertNotSame($t1, $t2);
    }
}
