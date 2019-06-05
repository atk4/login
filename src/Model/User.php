<?php
namespace atk4\login\Model;

use atk4\data\Model;
use atk4\login\PasswordManagement;

/**
 * Example user data model.
 */
class User extends Model
{

    use PasswordManagement;
    use Signups;

    public $table = 'user';

    public function init()
    {
        parent::init();

        $this->addField('name');
        $this->addField('email');
        $this->addField('password', ['\atk4\login\Field\Password']);

        $this->addField('role', ['enum'=>['user', 'admin']]);

        $this->initPasswordManagement();
    }
}
