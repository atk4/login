<?php
namespace atk4\login\Model;


class User extends \atk4\data\Model {
    public $table = 'user';

    function init()
    {
        parent::init();

        $this->addField('name');
        $this->addField('email');
        $this->addField('password', [
            'type'=>'password', 
            'serialize'=>'password_hash',
        ]);
    }


    /**
     * Extend to define additional validation rules
     */
    function verify($field, $unencrypted_password)
    {
        return (password_verify($unencrypted_password, $this[$field]));
    }
}
