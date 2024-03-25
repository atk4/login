<?php

declare(strict_types=1);

namespace Atk4\Login\Model;

use Atk4\Data\Model;
use Atk4\Data\Reference\HasOneSql;
use Atk4\Login\Feature\SetupAccessRuleModelTrait;

/**
 * Whitelist access control rules.
 */
class AccessRule extends Model
{
    use SetupAccessRuleModelTrait;

    public $table = 'login_access_rule';
    public $caption = 'Access Rule';

    /** @var array<mixed> Default Role model. */
    protected array $roleModelSeed = [Role::class];

    #[\Override]
    protected function init(): void
    {
        parent::init();

        /** @var HasOneSql */
        $r = $this->hasOne('role_id', [
            'model' => $this->roleModelSeed,
            'ourField' => 'role_id',
            'theirField' => 'id',
            'caption' => 'Role',
        ]);
        $r->addTitle();

        $this->addField('model'); // model class name

        /*
         * @TODO maybe all_visible and visible_fields can be replaced with just on field visible:
         *      '*' - equals all_fields=true
         *      'foo,bar' - equals visible_fields='foo,bar' or visible_fields=['foo', 'bar']
         *
         *      This way it also will be easier to merge permissions from multiple roles together (in future). For example:
         *          role_1 = 'f1, f2';
         *          role_2 = 'f2, f4';
         *          role_3 = '*';
         *          $actual_permissions = array_merge(explode(',', $role_1), explode(',', $role_2), explode(',' , $role_3));
         *          $actual_permissions = ['f1', 'f2', 'f4', '*'];
         *          and then apply array_search() to find if we allow all fields (*) or not
         *          $all_visible = array_search('*', $actual_permissions) !== false
         *          $visible_fields = array_diff($actual_fields, ['*']);
         */

        // which model fields should be visible
        $this->addField('all_visible', ['type' => 'boolean']);
        $this->addField('visible_fields'); // used if all_visible is false

        // which model fields should be editable
        $this->addField('all_editable', ['type' => 'boolean']);
        $this->addField('editable_fields'); // used if all_editable is false

        // which model actions are allowed
        $this->addField('all_actions', ['type' => 'boolean']);
        $this->addField('actions'); // used if all_actions is false

        // Specify which conditions will be applied on the model, e.g. "status=DRAFT AND sent=true OR status=SENT"
        // @TODO this will be replaced by JSON structure when Alain will develop such JS widget
        $this->addField('conditions');

        $this->setupAccessRuleModel();
    }
}
