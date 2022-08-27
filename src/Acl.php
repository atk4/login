<?php

declare(strict_types=1);

namespace Atk4\Login;

use Atk4\Data\Exception;
use Atk4\Data\Model;
use Atk4\Login\Model\AccessRule;
use Atk4\Login\Model\User;

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
     * @return AccessRule
     */
    public function getRules(Model $model)
    {
        /** @var User */
        $user = $this->auth->user;

        if (!$user->isLoaded()) {
            // user is not logged in - let's force him to do so. Alternative is to throw exception, but that's ugly.
            $this->auth->check();
            // throw new Exception('User should be logged in!');
        }

        $modelClasses = array_diff(class_implements($model), class_implements(Model::class));
        $class = get_class($model);
        do {
            if (!(new \ReflectionClass($class))->isAnonymous()) {
                $modelClasses[] = $class;
            }
        } while (($class = get_parent_class($class)) !== false);
        $res = $user->ref('AccessRules')->addCondition('model', 'in', $modelClasses);

        return $res; // @phpstan-ignore-line
    }

    /**
     * Given a model, this will apply some restrictions on it.
     *
     * Extend this method if you wish.
     */
    public function applyRestrictions(Model $m): void
    {
        foreach ($this->getRules($m) as $rule) {
            // extract as arrays
            $visible = is_array($rule->get('visible_fields')) ? $rule->get('visible_fields') : explode(',', $rule->get('visible_fields') ?? '');
            $editable = is_array($rule->get('editable_fields')) ? $rule->get('editable_fields') : explode(',', $rule->get('editable_fields') ?? '');
            $actions = is_array($rule->get('actions')) ? $rule->get('actions') : explode(',', $rule->get('actions') ?? '');

            // set visible and editable fields
            foreach ($m->getFields() as $name => $field) {
                if (!$rule->get('all_visible') && $visible) {
                    $field->ui['visible'] = array_search($name, $visible, true) !== false;
                }
                if (!$rule->get('all_editable') && $editable) {
                    $field->ui['editable'] = array_search($name, $editable, true) !== false;
                }
            }

            // remove not allowed actions
            if (!$rule->get('all_actions') && $actions) {
                $actions_to_remove = array_diff(array_keys($m->getUserActions()), $actions);
                foreach ($actions_to_remove as $action) {
                    $m->getUserAction($action)->enabled = false;
                }
            }

            // add conditions on model
            /* this will work in future when we will have json encoded condition structure stored in here
            if ($rule['conditions']) {
                $this->applyConditions($m, $rule['conditions']);
            }
            */
        }
    }

    /**
     * Apply conditions on model.
     *
     * @param mixed $conditions
     */
    public function applyConditions(Model $m, $conditions): void
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
