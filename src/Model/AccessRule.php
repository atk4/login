<?php


namespace atk4\login\Model;


use atk4\core\Exception;
use atk4\data\Model;

class AccessRule extends Model
{
    public $table = 'login_access_rule';

    /**
     * @throws Exception
     */
    function init() {
        parent::init();

        $this->hasOne('role', Role::class);

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
    }
}