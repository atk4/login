<?php

namespace atk4\login;

use atk4\core\Exception;
use atk4\data\Model;
use atk4\data\Persistence;

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
     * Returns AccessRules model for logged in user and in model scope.
     *
     * @param Model $model
     *
     * @return \atk4\login\Model\AccessRule
     */
    public function getRules(Model $model)
    {
        /** @var \atk4\login\Model\User*/
        $user = $this->auth->user;

        if (!$user->loaded()) {
            // user is not logged in - let's force him to do so. Alternative is to throw exception, but that's ugly.
            $this->auth->check();
            //throw new Exception('User should be logged in!');
        }

        return $user->ref('AccessRules')->addCondition('model', get_class($model));
    }

    /**
     * Given a model, this will apply some restrictions on it.
     *
     * Extend this method if you wish.
     *
     * @param Persistence $p
     * @param Model       $m
     */
    public function applyRestrictions(Persistence $p, Model $m)
    {
        foreach ($this->getRules($m) as $rule) {

            // set visible and editable fields
            foreach ($m->getFields() as $name => $field) {
                $field['ui']['visible'] = $rule['all_visible'] || (array_search($name, $rule['visible_fields']) !== false);
                $field['ui']['editable'] = $rule['all_editable'] || (array_search($name, $rule['editable_fields']) !== false);
            }

            // remove not allowed actions
            if (!$rule['all_actions'] && $rule['actions']) {
                $actions_to_remove = array_diff(array_keys($m->getActions()), $rule['actions']);
                foreach ($actions_to_remove as $action) {
                    $m->getAction($action)->enabled = false;
                }
            }

            // add conditions on model
            if ($rule['conditions']) {
                $this->applyConditions($p, $m, $rule['conditions']);
            }
        }
    }
    
    /**
     * Apply conditions on model.
     *
     * @param Persistence $p
     * @param Model       $m
     * @param mixed       $conditions
     */
    public function applyConditions(Persistence $p, Model $m, $conditions)
    {
        $m->addCondition($conditions);
    }

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
