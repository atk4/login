<?php

declare(strict_types=1);

namespace Atk4\Login\Model;

use Atk4\Data\Field\PasswordField;
use Atk4\Data\Model;
use Atk4\Data\Reference\HasOneSql;
use Atk4\Login\Feature\PasswordManagementTrait;
use Atk4\Login\Feature\SendEmailActionTrait;
use Atk4\Login\Feature\SetupUserModelTrait;
use Atk4\Login\Feature\SignupTrait;

class User extends Model
{
    use PasswordManagementTrait;
    use SendEmailActionTrait;
    use SetupUserModelTrait;
    use SignupTrait;

    public $table = 'login_user';
    public $caption = 'User';

    #[\Override]
    protected function init(): void
    {
        parent::init();

        $this->addField('name');
        $this->addField('email');
        $this->addField('password', [PasswordField::class]);

        // currently user can have only one role. In future it should be n:n relation
        /** @var HasOneSql */
        $r = $this->hasOne('role_id', [
            'model' => [Role::class],
            'ourField' => 'role_id',
            'theirField' => 'id',
            'caption' => 'Role',
        ]);
        $r->addTitle();

        $this->setupUserModel();
        $this->initSignup();
        $this->initSendEmailAction();
        $this->initPasswordManagement();
    }
}
