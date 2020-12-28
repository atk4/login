<?php

declare(strict_types=1);

namespace Atk4\Login\Tests;

use Atk4\Login\Model\AccessRule;
use Atk4\Login\Model\Role;
use Atk4\Login\Model\User;

abstract class Generic extends \Atk4\Schema\PhpunitTestCase
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
                1 => ['id' => 1, 'role_id' => 1, 'model' => '\\Atk4\Login\\Model\\User', 'all_visible' => 1, 'visible_fields' => null, 'all_editable' => 0, 'editable_fields' => null, 'all_actions' => 1, 'actions' => null, 'conditions' => null],
                2 => ['id' => 2, 'role_id' => 2, 'model' => '\\Atk4\Login\\Model\\User', 'all_visible' => 1, 'visible_fields' => null, 'all_editable' => 1, 'editable_fields' => null, 'all_actions' => 1, 'actions' => null, 'conditions' => null],
                3 => ['id' => 3, 'role_id' => 2, 'model' => '\\Atk4\Login\\Model\\Role', 'all_visible' => 1, 'visible_fields' => null, 'all_editable' => 1, 'editable_fields' => null, 'all_actions' => 1, 'actions' => null, 'conditions' => null],
            ],
        ]);
    }

    protected function getUserModel()
    {
        return new User($this->db, ['table' => 'login_user']);
    }

    protected function getRoleModel()
    {
        return new Role($this->db, ['table' => 'login_role']);
    }

    protected function getAccessRuleModel()
    {
        return new AccessRule($this->db, ['table' => 'login_access_rule']);
    }
}
