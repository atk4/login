<?php

namespace atk4\login\Feature;

/**
 * Adding this trait to your atk4/login models will properly setup these models for your application. Additionally execute
 * $this->setupModel() from your models init() method after you define model fields.
 *
 * @package atk4\login\Feature
 */
use atk4\login\Model\AccessRule;
use atk4\login\Model\Role;
use atk4\login\Model\User;

use atk4\login\FormField;

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
            public function init()
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

        $this->getField('visible_fields')->ui['form'] = FormField\FieldsDropDown::class;
        $this->getField('editable_fields')->ui['form'] = FormField\FieldsDropDown::class;
        $this->getField('actions')->ui['form'] = FormField\ActionsDropDown::class;
        $this->getField('conditions')->type = 'text';

        // cleanup data
        $this->addHook('beforeSave', function ($m) {
            if ($m['all_visible']) {
                $m['visible_fields'] = null;
            }
            if ($m['all_editable']) {
                $m['editable_fields'] = null;
            }
            if ($m['all_actions']) {
                $m['actions'] = null;
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
        $this->addHook('beforeSave', function ($m) {
            // password should be set when trying to insert new record
            // but it can be empty if you update record (then it will not change password)
            if (!$m->loaded() && !$m->get('password')) {
                throw new ValidationException(['password' => 'Password is required'], $this);
            }
        });
    }
}
