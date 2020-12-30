<?php

declare(strict_types=1);

namespace Atk4\Login\Tests;

use Atk4\Data\Model;
use Atk4\Login\Auth;
use Atk4\Login\Model\AccessRule;
use Atk4\Login\Model\Role;
use Atk4\Login\Model\User;

class AuthTest extends Generic
{
    public function testDb()
    {
        $this->setupDefaultDb();

        $u = $this->getUserModel();
        $this->assertSame(2, count($u->export()));

        $r = new Role($this->db, ['table' => 'login_role']);
        $this->assertSame(2, count($r->export()));

        $a = new AccessRule($this->db, ['table' => 'login_access_rule']);
        $this->assertSame(3, count($a->export()));

        // password field should not be visible in UI by default
        $this->assertFalse($u->getField('password')->isVisible());

        // password field value should not load when loading record
        $this->assertNull((clone $u)->load(1)->get('password'));

        // test traversal
        $this->assertSame(2, count((clone $u)->load(2)->ref('AccessRules')->export()));
        $this->assertSame(1, count((clone $u)->load(2)->ref('role_id')->export()));
        $this->assertSame(2, count((clone $r)->load(2)->ref('AccessRules')->export()));
        $this->assertSame(1, count((clone $r)->load(2)->ref('Users')->export()));
        $this->assertSame(1, count((clone $a)->load(2)->ref('role_id')->export()));
    }

    public function testAuth($cacheEnabled = true)
    {
        $this->setupDefaultDb();

        // test Auth without automatic check to avoid UI involvement (login form, user menu etc)
        $auth = new Auth(['check' => false, 'cacheEnabled' => $cacheEnabled]);
        $auth->setModel($this->getUserModel());
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
        $auth->setModel($this->getUserModel());

        $auth->tryLogin('user', 'user');
        $this->assertTrue($auth->isLoggedIn());
        $this->assertSame('user', $auth->user->get($auth->fieldLogin));
        $auth->user->set('name', 'Test User');
        $this->assertSame('Test User', $auth->user->get('name'));
        $auth->user->save();
        $this->assertSame('Test User', $auth->user->get('name'));

        $auth->setModel($this->getUserModel());
        $auth->tryLogin('user', 'user');
        $this->assertTrue($auth->isLoggedIn());
        $this->assertSame('user', $auth->user->get($auth->fieldLogin));
        $this->assertSame('Test User', $auth->user->get('name'));

        // now create new Auth object, set model and see if it will pick up
        // last logged user from cache
        if ($cacheEnabled) {
            $auth = new Auth(['check' => false, 'cacheEnabled' => $cacheEnabled]);
            $auth->setModel($this->getUserModel());
            $this->assertTrue($auth->isLoggedIn());
            $this->assertSame('user', $auth->user->get($auth->fieldLogin));
            $this->assertSame('Test User', $auth->user->get('name'));
        }

        // custom login and password fields
        $auth = new Auth(['check' => false, 'cacheEnabled' => $cacheEnabled]);

        $auth->setModel($this->getUserModel(), 'name', 'password');
        $auth->tryLogin('admin', 'admin'); // name<>admin
        $this->assertFalse($auth->isLoggedIn());

        $auth->setModel($this->getUserModel(), 'name', 'password');
        $auth->tryLogin('Administrator', 'admin'); // name=Administrator
        $this->assertTrue($auth->isLoggedIn());

        $auth->setModel($this->getUserModel(), 'email', 'name');
        $auth->tryLogin('admin', 'admin'); // wrong password field
        $this->assertFalse($auth->isLoggedIn());

        // @todo Need some tests for cache session expireTime property and cache expiration
        if ($cacheEnabled) {
            $auth = new Auth([
                'check' => false,
                'cacheEnabled' => $cacheEnabled,
                'cacheOptions' => ['expireTime' => 2], // 2 seconds
            ]);

            $auth->setModel($this->getUserModel());
            $auth->tryLogin('admin', 'admin'); // saves in cache and set timer

            $auth->setModel($this->getUserModel());
            $this->assertTrue($auth->isLoggedIn());

            // now sleep 3 seconds (cache should expire) and try again
            sleep(3);
            $auth->setModel($this->getUserModel());
            $this->assertFalse($auth->isLoggedIn());
        }
    }

    public function testAuthNoCache()
    {
        $this->testAuth(false);
    }
}
