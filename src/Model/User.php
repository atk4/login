<?php
namespace atk4\login\Model;

/**
 * Example user data model.
 */
class User extends \atk4\data\Model
{
    public $table = 'user';

    public function init()
    {
        parent::init();

        $this->addField('name');
        $this->addField('email');
        $this->addField('is_admin', ['type'=>'boolean']);
        $this->addField('password', ['\atk4\login\Field\Password']);
    }
}
