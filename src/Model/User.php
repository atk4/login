<?php

declare(strict_types=1);

namespace Atk4\Login\Model;

use Atk4\Data\Model;
// Features of User model
use Atk4\Login\Feature\PasswordManagement;
use Atk4\Login\Feature\SendEmailAction;
use Atk4\Login\Feature\SetupModel;
use Atk4\Login\Feature\Signup;
use Atk4\Login\Feature\UniqueFieldValue;
use Atk4\Login\Field\Password;

/**
 * Example user data model.
 */
class User extends Model
{
    use PasswordManagement;
    use SendEmailAction;
    use SetupModel;
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
        $this->initSendEmailAction();
        $this->initPasswordManagement();
    }
}
