<?php

declare(strict_types=1);

namespace Atk4\Login\Feature;

use Atk4\Data\Model;
use Atk4\Login\Form\Control;
use Atk4\Login\Model\AccessRule;
use Atk4\Login\Model\Role;
use Atk4\Login\Model\User;

/*
 * Adding this trait to your atk4/login models will properly setup these models for your application. Additionally execute
 * $this->setupModel() from your models init() method after you define model fields.
 */
trait SetupModelTrait
{
    public function setupAccessRuleModel(): void
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

    public function setupRoleModel(): void
    {
        $this->getField('name')->required = true;
        $this->setUnique('name');
    }

    public function setupUserModel(): void
    {
        $this->getField('name')->required = true;
        $this->getField('email')->required = true;
        $this->setUnique('email');
        $this->getField('password')->required = true;
        $this->getField('password')->ui['visible'] = false;

        // all AccessRules for all user roles
        // @TODO in future when there can be multiple, then merge them together
        $this->hasMany('AccessRules', [
            'model' => function ($m) {
                return $m->ref('role_id')->ref('AccessRules');
            },
            'our_field' => 'role_id',
            'their_field' => 'role_id',
        ]);
    }
}
