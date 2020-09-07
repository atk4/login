<?php

declare(strict_types=1);

namespace atk4\login;

use atk4\core\Exception;
use atk4\data\Model;
use atk4\data\Persistence;

/**
 * Access Control Layer. Create one and pass it to your Auth controller.
 */
class Acl
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
     * @return \atk4\login\Model\AccessRule
     */
    public function getRules(Model $model)
    {
        /** @var \atk4\login\Model\User */
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
     */
    public function applyRestrictions(Persistence $p, Model $m)
    {
        foreach ($this->getRules($m) as $rule) {
            // extract as arrays
            $visible = is_array($rule->get('visible_fields')) ? $rule->get('visible_fields') : explode(',', $rule->get('visible_fields'));
            $editable = is_array($rule->get('editable_fields')) ? $rule->get('editable_fields') : explode(',', $rule->get('editable_fields'));
            $actions = is_array($rule->get('actions')) ? $rule->get('actions') : explode(',', $rule->get('actions'));

            // set visible and editable fields
            foreach ($m->getFields() as $name => $field) {
                $field->ui['visible'] = $rule->get('all_visible') || (array_search($name, $visible, true) !== false);
                $field->ui['editable'] = $rule->get('all_editable') || (array_search($name, $editable, true) !== false);
            }

            // remove not allowed actions
            if (!$rule->get('all_actions') && $rule->get('actions')) {
                $actions_to_remove = array_diff(array_keys($m->getUserActions()), $actions);
                foreach ($actions_to_remove as $action) {
                    $m->getUserAction($action)->enabled = false;
                }
            }

            // add conditions on model
            /*
             *  this will work in future when we will have json encoded condition structure stored in here
             *  for now let's comment this out
            if ($rule['conditions']) {
                $this->applyConditions($p, $m, $rule['conditions']);
            }
            */
        }
    }

    /**
     * Apply conditions on model.
     *
     * @param mixed $conditions
     */
    public function applyConditions(Persistence $p, Model $m, $conditions)
    {
        $m->addCondition($conditions);
    }

    // Call $app->acl->can('admin'); for example to find out if user is allowed to admin things.
    /*
    public function can($feature)
    {
        if (!$this->permissions) {
            $this->cachePermissions();
        }

        return $this->permissions[$feature] ?? false;
    }
    */
}
