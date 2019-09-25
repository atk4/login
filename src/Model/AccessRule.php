<?php
namespace atk4\login\Model;

use atk4\core\Exception;
use atk4\data\Model;

/**
 * White-list access control rules.
 */
class AccessRule extends Model
{
    public $table = 'login_access_rule';
    public $caption = 'Access Rule';

    /**
     * @throws Exception
     */
    public function init()
    {
        parent::init();

        $this->hasOne('role_id', [Role::class, 'our_field'=>'role_id', 'their_field'=>'id', 'caption'=>'Role']);

        $this->addField('model', ['required'=>true, 'caption'=>'Model Class name']); // model class name

        /*
        $this->containsOne('config', new class extends Model {
            public function init()
            {
                parent::init();

                // put all fields which are below in here
                // And this new class should be separated to let's say AccessRule/Model class so we can
                // also have AccessRule/Interface or AccessRule/View or AccessRule/Page class in future
                // with different config properties

            }
        });
        */

        // which model fields should be visible
        $this->addField('all_visible', ['type'=>'boolean', 'default'=>true]);
        //$this->addField('visible_fields', ['type'=>'array']);//, 'ui'=>['form'=>'FormField/MultiLine']]); // used if all_visible is false

        // which model fields should be editable
        $this->addField('all_editable', ['type'=>'boolean', 'default'=>true]);
        //$this->addField('editable_fields', ['type'=>'array']); // used if all_editable is false

        // which model actions are allowed
        $this->addField('all_actions', ['type'=>'boolean', 'default'=>true]);
        //$this->addField('actions', ['type'=>'array']); // used if all_actions is false

        // Specify which conditions will be applied on the model, e.g. "status=DRAFT"
        // Conditions are always joined with AND, like status=DRAFT AND sent=false
        $this->containsMany('conditions', new class extends Model {
            public function init()
            {
                parent::init();
                $this->addField('field', ['required'=>true]);
                $this->addField('cond'); // condition
                $this->addField('value');
            }
        });
    }
}
