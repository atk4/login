<?php
namespace atk4\login\Model;

use atk4\data\Model;

class Role extends Model
{
    public $table = 'login_role';

    public function init()
    {
        parent::init();

        $this->addField('name', ['type'=>'string', 'required'=>true]);

        $this->hasMany('Users', User::class);
        $this->hasMany('AccessRules', AccessRule::class);
    }
}
