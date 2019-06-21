<?php
namespace atk4\login\Model;

use atk4\data\Model;

# Features of User model
use atk4\login\Feature\Signup;
use atk4\login\Feature\PasswordManagement;

/**
 * Example user data model.
 */
class User extends Model
{

    use PasswordManagement;
    use Signup;

    public $table = 'login_user';

    public function init()
    {
        parent::init();

        $this->addField('name');
        $this->addField('email');
        $this->addField('password', ['\atk4\login\Field\Password']);

        $this->hasOne('role', Role::class);

        $this->initSignup();
        $this->initPasswordManagement();
    }
}
