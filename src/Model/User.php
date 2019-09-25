<?php
namespace atk4\login\Model;

use atk4\data\Model;
use atk4\data\ValidationException;
use atk4\login\Field\Password;

# Features of User model
use atk4\login\Feature\Signup;
use atk4\login\Feature\PasswordManagement;
use atk4\login\Feature\UniqueFieldValue;

/**
 * Example user data model.
 */
class User extends Model
{
    use PasswordManagement;
    use Signup;
    use UniqueFieldValue;

    public $table = 'login_user';
    public $caption = 'User';

    public function init()
    {
        parent::init();

        $this->initSignup();
        $this->initPasswordManagement();

        $this->addField('name', ['required' => true]);
        $this->addField('email', ['required' => true]);
        $this->setUnique('email');
        $this->addField('password', [Password::class]);

        // currently user can have only one role. In future it should be n:n relation
        $this->hasOne('role_id', [Role::class, 'our_field'=>'role_id', 'their_field'=>'id', 'caption'=>'Role'])->withTitle();

        // all AccessRules for all user roles merged together
        $this->hasMany('AccessRules', [
            function ($m) {
                return $m->ref('role_id')->ref('AccessRules');
            },
            'our_field' => 'id',
            'their_field' => 'role_id',
        ]);

        // add some validations
        $this->addHook('beforeSave', function ($m){
            // password should be set when trying to insert new record
            // but it can be empty if you update record (then it will not change password)
            if (!$m->loaded() && !$m->get('password')) {
                throw new ValidationException(['password' => 'Password is required'], $this);
            }
        });
    }
}
