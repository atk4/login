<?php

declare(strict_types=1);

namespace Atk4\Login\Feature;

use Atk4\Data\Model\UserAction;

/**
 * Adding this trait to your user model will allow users to sign-up for your application. Additionally execute
 * $this->initSignup() from your init() method.
 */
trait SignupTrait
{
    /**
     * Adds register_new_user action.
     */
    public function initSignup()
    {
        $this->addUserAction('register_new_user', ['appliesTo' => UserAction::APPLIES_TO_NO_RECORDS, 'fields' => ['name', 'email', 'password']]);
    }

    /**
     * Creates new user record.
     *
     * @param array $data Optionally can pass field values of User model
     */
    public function register_new_user($data = [])
    {
        $this->save($data);
    }
}
