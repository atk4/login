<?php

declare(strict_types=1);

namespace atk4\login\tests;

use atk4\data\Exception;
use atk4\data\Model;
use atk4\login\Auth;
use atk4\login\Model\AccessRule;
use atk4\login\Model\Role;
use atk4\login\Model\User;

class AuthTest extends \atk4\schema\PhpunitTestCase
{
    protected function setupDefaultDb()
    {
        $this->setDb([
            'login_user' => [
                1 => ['id' => 1, 'name' => 'Standard User', 'email' => 'user', 'password' => '$2y$10$BwEhcP8f15yOexf077VTHOnySn/mit49ZhpfeBkORQhrsmHr4U6Qy', 'role_id' => 1], // user/user
                2 => ['id' => 2, 'name' => 'Administrator', 'email' => 'admin', 'password' => '$2y$10$p34ciRcg9GZyxukkLIaEnenGBao79fTFa4tFSrl7FvqrxnmEGlD4O', 'role_id' => 2], // admin/admin
            ],
            'login_role' => [
                1 => ['id' => 1, 'name' => 'User Role'],
                2 => ['id' => 2, 'name' => 'Admin Role'],
            ],
            'login_access_rule' => [
                1 => ['id' => 1, 'role_id' => 1, 'model' => '\\atk4\login\\Model\\User', 'all_visible' => 1, 'visible_fields' => null, 'all_editable' => 0, 'editable_fields' => null, 'all_actions' => 1, 'actions' => null, 'conditions' => null],
                2 => ['id' => 2, 'role_id' => 2, 'model' => '\\atk4\login\\Model\\User', 'all_visible' => 1, 'visible_fields' => null, 'all_editable' => 1, 'editable_fields' => null, 'all_actions' => 1, 'actions' => null, 'conditions' => null],
                3 => ['id' => 3, 'role_id' => 2, 'model' => '\\atk4\login\\Model\\Role', 'all_visible' => 1, 'visible_fields' => null, 'all_editable' => 1, 'editable_fields' => null, 'all_actions' => 1, 'actions' => null, 'conditions' => null],
            ],
        ]);
    }

    protected function getUserModel()
    {
        return new User($this->db, 'login_user');
    }

    public function testDb()
    {
        $this->setupDefaultDb();

        $u = $this->getUserModel();
        $this->assertEquals(2, count($u->export()));

        $r = new Role($this->db, 'login_role');
        $this->assertEquals(2, count($r->export()));

        $a = new AccessRule($this->db, 'login_access_rule');
        $this->assertEquals(3, count($a->export()));

        // password field should not be visible in UI by default
        $this->assertEquals(false, $u->getField('password')->isVisible());

        // password field value should not load when loading record
        $this->assertEquals(null, (clone $u)->load(1)->get('password'));

        // test traversal
        $this->assertEquals(2, count((clone $u)->load(2)->ref('AccessRules')->export()));
        $this->assertEquals(1, count((clone $u)->load(2)->ref('role_id')->export()));
        $this->assertEquals(2, count((clone $r)->load(2)->ref('AccessRules')->export()));
        $this->assertEquals(1, count((clone $r)->load(2)->ref('Users')->export()));
        $this->assertEquals(1, count((clone $a)->load(2)->ref('role_id')->export()));
    }

    public function testAuth($cacheEnabled = true)
    {
        $this->setupDefaultDb();

        // test Auth without automatic check to avoid UI involvement (login form, user menu etc)
        $auth = new Auth(['check' => false, 'cacheEnabled' => $cacheEnabled]);
        $auth->setModel($u = $this->getUserModel());
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
        $auth->setModel($u = $this->getUserModel());

        $auth->tryLogin('user', 'user');
        $this->assertTrue($auth->isLoggedIn());
        $this->assertSame('user', $auth->user->get($auth->fieldLogin));
        $u->set('name', 'Test User');
        $this->assertSame('Test User', $u->get('name'));
        $u->save();
        $this->assertSame('Test User', $u->get('name'));

        $auth->setModel($u = $this->getUserModel());
        $auth->tryLogin('user', 'user');
        $this->assertTrue($auth->isLoggedIn());
        $this->assertSame('user', $auth->user->get($auth->fieldLogin));
        $this->assertSame('Test User', $u->get('name'));

        // now create new Auth object, set model and see if it will pick up
        // last logged user from cache
        if ($cacheEnabled) {
            $auth = new Auth(['check' => false, 'cacheEnabled' => $cacheEnabled]);
            $auth->setModel($u = $this->getUserModel());
            $this->assertTrue($auth->isLoggedIn());
            $this->assertSame('user', $auth->user->get($auth->fieldLogin));
            $this->assertSame('Test User', $u->get('name'));
            $this->assertSame('Test User', $auth->user->get('name'));
        }

        // custom login and password fields
        $auth = new Auth(['check' => false, 'cacheEnabled' => $cacheEnabled]);

        $auth->setModel($u = $this->getUserModel(), 'name', 'password');
        $auth->tryLogin('admin', 'admin'); // name<>admin
        $this->assertFalse($auth->isLoggedIn());

        $auth->setModel($u = $this->getUserModel(), 'name', 'password');
        $auth->tryLogin('Administrator', 'admin'); // name=Administrator
        $this->assertTrue($auth->isLoggedIn());

        $auth->setModel($u = $this->getUserModel(), 'email', 'name');
        $auth->tryLogin('admin', 'admin'); // wrong password field
        $this->assertFalse($auth->isLoggedIn());
    }

    public function testAuthNoCache()
    {
        $this->testAuth(false);
    }
}
