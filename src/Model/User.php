<?php

declare(strict_types=1);

namespace Atk4\Login\Model;

use Atk4\Data\Field\Password;
use Atk4\Data\Model;
use Atk4\Login\Feature\PasswordManagementTrait;
use Atk4\Login\Feature\SendEmailActionTrait;
use Atk4\Login\Feature\SetupUserModelTrait;
use Atk4\Login\Feature\SignupTrait;

/**
 * Example user data model.
 */
class User extends Model
{
    use PasswordManagementTrait;
    use SendEmailActionTrait;
    use SetupUserModelTrait;
    use SignupTrait;

    public $table = 'login_user';
    public $caption = 'User';

    protected function init(): void
    {
        parent::init();

        $this->addField('name');
        $this->addField('email');
        $this->addField('password', [Password::class]);

        // currently user can have only one role. In future it should be n:n relation
        $this->hasOne('role_id', ['model' => [Role::class], 'our_field' => 'role_id', 'their_field' => 'id', 'caption' => 'Role'])
            ->addTitle();

        $this->setupUserModel();
        $this->initSignup();
        $this->initSendEmailAction();
        $this->initPasswordManagement();
    }
}
