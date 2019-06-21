<?php


namespace atk4\login\Model;


use atk4\data\Model;

class Role extends Model
{
    //public $id_field = 'role';
    public $title_field = 'role';
    public $table = 'login_role';

    function init()
    {
        parent::init();

        $this->addField('role', ['type'=>'string']);

        $this->getField('role')->type='string';

        $this->hasMany('Users', [User::class, 'their_field'=>'role', 'our_field'=>'role']);
        $this->hasMany('AccessRules', [AccessRule::class, 'their_field'=>'role', 'our_field'=>'role']);
    }
}