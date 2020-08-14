<?php

namespace atk4\login\Feature;

use atk4\data\Model\UserAction;

/**
 * Adding this trait to your user model will allow users to sign-up for your application. Additionally execute
 * $this->initSignup() from your init() method.
 *
 * @package atk4\login\Feature
 */
trait Signup
{
    /**
     * Adds register_new_user action.
     */
    public function initSignup()
    {
        $this->addUserAction('register_new_user', ['appliesTo' => \atk4\data\Model\UserAction::APPLIES_TO_NO_RECORDS]);
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
