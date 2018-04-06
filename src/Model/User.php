<?php
namespace atk4\login\Model;


class User extends \atk4\data\Model {
    public $table = 'user';

    function init()
    {
        parent::init();

        $this->addField('name');
        $this->addField('email');
        $this->addField('is_admin', ['type'=>'boolean']);
        $this->addField('password', ['\atk4\login\Field\Password']);
    }
}
