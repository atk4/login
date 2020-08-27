<?php

declare(strict_types=1);

namespace atk4\login\Feature;

/*
 * Adding this trait to your atk4/login models will properly setup these models for your application. Additionally execute
 * $this->setupModel() from your models init() method after you define model fields.
 *
 * @package atk4\login\Feature
 */
use atk4\data\Model;
use atk4\login\Form\Control;
use atk4\login\Model\AccessRule;
use atk4\login\Model\Role;
use atk4\login\Model\User;

trait SetupModel
{
    /**
     * Setup AccessRule model.
     */
    public function setupAccessRuleModel()
    {
        $this->getField('model')->required = true;
        $this->getField('model')->caption = 'Model Class';

        /*
        $this->containsOne('config', new class extends Model {
            protected function init()
            {
                parent::init();

                // We can put all fields which are below in here.
                // And this new class should be separated to let's say AccessRule/Model class so we can
                // also have AccessRule/Interface or AccessRule/View or AccessRule/Page class in future
                // with different config properties

            }
        });
        */

        $this->getField('all_visible')->default = true;
        $this->getField('all_editable')->default = true;
        $this->getField('all_actions')->default = true;

        $this->getField('visible_fields')->ui['form'] = [Control\Fields::class];
        $this->getField('editable_fields')->ui['form'] = [Control\Fields::class];
        $this->getField('actions')->ui['form'] = [Control\Actions::class];
        $this->getField('conditions')->type = 'text';

        // cleanup data
        $this->onHook(Model::HOOK_BEFORE_SAVE, function ($m) {
            if ($m->get('all_visible')) {
                $m->setNull('visible_fields');
            }
            if ($m->get('all_editable')) {
                $m->setNull('editable_fields');
            }
            if ($m->get('all_actions')) {
                $m->setNull('actions');
            }
        });
    }

    /**
     * Setup Role model.
     */
    public function setupRoleModel()
    {
        $this->getField('name')->required = true;
        $this->setUnique('name');
    }

    /**
     * Setup User model.
     */
    public function setupUserModel()
    {
        $this->getField('name')->required = true;
        $this->getField('email')->required = true;
        $this->setUnique('email');
        $this->getField('password')->ui['visible'] = false;

        // all AccessRules for all user roles
        // @TODO in future when there can be multiple, then merge them together
        $this->hasMany('AccessRules', [
            function ($m) {
                return $m->ref('role_id')->ref('AccessRules');
            },
            'our_field' => 'role_id',
            'their_field' => 'role_id',
        ]);

        // add some validations
        $this->onHook(Model::HOOK_BEFORE_SAVE, function ($m) {
            // password should be set when trying to insert new record
            // but it can be empty if you update record (then it will not change password)
            if (!$m->loaded() && !$m->get('password')) {
                throw new ValidationException(['password' => 'Password is required'], $this);
            }
        });
    }
}
