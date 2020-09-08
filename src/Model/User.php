<?php

declare(strict_types=1);

namespace atk4\login\Model;

use atk4\data\Model;
use atk4\login\Feature\PasswordManagement;
// Features of User model
use atk4\login\Feature\SetupModel;
use atk4\login\Feature\Signup;
use atk4\login\Feature\UniqueFieldValue;
use atk4\login\Field\Password;

/**
 * Example user data model.
 */
class User extends Model
{
    use SetupModel;
    use PasswordManagement;
    use Signup;
    use UniqueFieldValue;

    public $table = 'login_user';
    public $caption = 'User';

    protected function init(): void
    {
        parent::init();

        $this->addField('name');
        $this->addField('email');
        $this->addField('password', [Password::class]);

        // currently user can have only one role. In future it should be n:n relation
        $this->hasOne('role_id', [Role::class, 'our_field' => 'role_id', 'their_field' => 'id', 'caption' => 'Role'])->withTitle();

        // traits
        $this->setupUserModel();
        $this->initSignup();
        $this->initPasswordManagement();
    }
}
