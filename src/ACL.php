<?php

namespace atk4\login;

/**
 * Access Control Layer. Create one and pass it to your Auth controller.
 */
class ACL
{
    /**
     * References an auth controller, so we can look up who is logged
     * in and what their permissions are.
     *
     * @var Auth
     */
    public $auth;

    /**
     * Given a model, this will apply some restrictions on it.
     *
     * @param \atk4\data\Persistence $p
     * @param \atk4\data\Model       $m
     */
    public function applyRestrictions(\atk4\data\Persistence $p, \atk4\data\Model $m)
    {
        // Extend this method
        // if($m instanceof Model\User && !$this->auth->user['is_admin']) {
        //      $m->getField('is_admin')->read_only = true;
        // }
    }

    /*
    protected $permissions = [];

    /**
     * Gather permissions of currently logged in users for faster access
     */
    /*
    public function cachePermissions()
    {
        $this->permissions = [
            //'admin' => $this->auth->user->loaded() && $this->auth->user['is_admin'] // Imants: disabled this to not fail miserably
        ];
    }
    */

    /**
     * Call $app->acl->can('admin'); for example to find out if user is allowed to admin things.
     */
    /*
    public function can($feature)
    {
        if (!$this->permissions) {
            $this->cachePermissions();
        }

        return $this->permissions[$feature] ?? false;
    }
    */

    /**
     * Will apply per-model modifications (after it's initialized) which will take permissions
     * into account.
     */
    /*
    public function applyRestrictions(Persistence $p, Model $m)
    {
        if($m instanceof User && !$this->can('admin')) {
            $m->getField('email')->read_only = true;
        }

        if($m instanceof Admins && !$this->can('admin')) {
            throw Exception();
        }
    }
    */
}
