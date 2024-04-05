<?php

declare(strict_types=1);

namespace Atk4\Login;

use Atk4\Data\Exception;
use Atk4\Data\Model;
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
     * Internal property to switch off ACL.
     * Used for ACL models themself because ACL internally always needs full access to its models.
     */
    private bool $disabled = false;

    /**
     * Returns array of AccessRules records for logged in user and in particular model scope.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRules(Model $model): array
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

        // Internally disable ACL for a moment and limit to only required fields to avoid recursion
        $this->disabled = true;
        $rules = $user->ref('AccessRules')
            ->addCondition('model', 'in', $modelClasses)
            ->export(['model', 'all_visible', 'visible_fields', 'all_editable', 'editable_fields', 'all_actions', 'actions', 'conditions']);

        // normalize
        foreach ($rules as $k => $rule) {
            $rules[$k]['visible_fields'] = $rule['all_visible'] ? [] : $this->normalizeValue($rule, 'visible_fields');
            $rules[$k]['editable_fields'] = $rule['all_editable'] ? [] : $this->normalizeValue($rule, 'editable_fields');
            $rules[$k]['actions'] = $rule['all_actions'] ? [] : $this->normalizeValue($rule, 'actions');
        }

        $this->disabled = false;

        return $rules;
    }

    private function normalizeValue(array $rule, string $field): array
    {
        $v = $rule[$field] ?? [];

        return is_array($v) ? $v : explode(',', $v);
    }

    /**
     * Given a model, this will apply some restrictions on it.
     *
     * Extend this method if you wish.
     */
    public function applyRestrictions(Model $m): void
    {
        if ($this->disabled) {
            return;
        }

        foreach ($this->getRules($m) as $rule) {
            // set visible and editable fields
            foreach ($m->getFields() as $name => $field) {
                if ($rule['visible_fields']) {
                    $field->ui['visible'] = array_search($name, $rule['visible_fields'], true) !== false;
                }
                if ($rule['editable_fields']) {
                    $field->ui['editable'] = array_search($name, $rule['editable_fields'], true) !== false;
                }
            }

            // remove not allowed actions
            if ($rule['actions']) {
                $actions_to_remove = array_diff(array_keys($m->getUserActions()), $rule['actions']);
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
