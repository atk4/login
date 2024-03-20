<?php

declare(strict_types=1);

namespace Atk4\Login\Feature;

use Atk4\Data\Model;
use Atk4\Login\Form\Control;

trait SetupAccessRuleModelTrait
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
        $this->onHook(Model::HOOK_BEFORE_SAVE, static function (self $m) {
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
}
