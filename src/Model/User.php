<?php
namespace atk4\login\Model;

use atk4\data\Model;
use atk4\login\Field\Password;

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

        $this->addField('name', ['required' => true]);
        $this->addField('email', ['required' => true]);
        $this->addField('password', [Password::class]);

        $this->hasOne('role_id', Role::class);
        $this->hasMany('AccessRules', function ($m) {
            return $m->refLink('role_id')->ref('AccessRules');
        });

        $this->initSignup();
        $this->initPasswordManagement();
    }
}
