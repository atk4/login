<?php
namespace atk4\login\Model;


class User extends \atk4\data\Model {
    public $table = 'user';

    function init()
    {
        parent::init();

        $this->addField('name');
        $this->addField('email');
        $this->addField('password', ['\atk4\login\Field\Password']);
    }
}
