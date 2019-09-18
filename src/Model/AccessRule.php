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

    /**
     * @throws Exception
     */
    public function init()
    {
        parent::init();

        $this->hasOne('role_id', Role::class);

        $this->addField('model', ['required'=>true]); // model class name

        // which model fields should be visible
        $this->addField('all_visible', ['type'=>'boolean', 'default'=>true]);
        $this->addField('visible_fields', ['type'=>'array']); // if all_visible is false

        // which model fields should be editable
        $this->addField('all_editable', ['type'=>'boolean', 'default'=>true]);
        $this->addField('editable_fields', ['type'=>'array']); // if all_editable is false

        // which model actions are allowed
        $this->addField('all_actions', ['type'=>'boolean', 'default'=>true]);
        $this->addField('actions', ['type'=>'array']); // if all_actions is false

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

        /*
        $this->addField('priority', ['type'=>'integer', 'default'=>1]);
        $this->addField('action', ['enum'=>['allow', 'deny']]);

        $this->containsMany('model_defs', new class extends Model {
            public $caption='ModelDef';
            function init() {
                parent::init();

                $this->addField('model');

                // Specify which conditions will be applied on the model, e.g. "status=DRAFT"
                $this->containsMany('conditions', new class extends Model {
                    function init() {
                        parent::init();
                        $this->addField('field');
                        $this->addField('value');
                    }
                });

                $this->addField('all_fields', ['type'=>'boolean']);
                $this->addField('fields', ['type'=>'array']); // if all_fields is false

                $this->addField('all_actions', ['type'=>'boolean']);
                $this->addField('actions', ['type'=>'array']); // if all_actions is false
            }
        });
        */