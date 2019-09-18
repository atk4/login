<?php
namespace atk4\login\demo;

use atk4\data\Model;
use atk4\data\Persistence;
use atk4\login\ACL;
use atk4\login\Model\Admins;
use atk4\login\Model\User;

class TestACL extends ACL
{
    protected $permissions = [];

    /**
     * Gather permissions of currently logged in users for faster access
     */
    public function cachePermissions()
    {
        $this->permissions = [
            'admin' => $this->auth->user->loaded() && $this->auth->user['is_admin']
        ];
    }

    /**
     * Call $app->acl->can('admin'); for example to find out if user is allowed to admin things.
     */
    public function can($feature)
    {
        if (!$this->permissions) {
            $this->cachePermissions();
        }

        return $this->permissions[$feature];
    }


    /**
     * Will apply per-model modifications (after it's initialized) which will take permissions
     * into account.
     */
    public function applyRestrictions(Persistence $p, Model $m)
    {
        if($m instanceof User && !$this->can('admin')) {
            $m->getField('email')->read_only = true;
        }

        if($m instanceof Admins && !$this->can('admin')) {
            throw Exception();
        }
    }
}
