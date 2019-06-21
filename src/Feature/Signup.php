<?php


namespace atk4\login\Feature;


/**
 * Adding this trait to your user model will allow users to sign-up for your application. Additionally execute
 * $this->initSignup() from yoru init() method.
 *
 * @package atk4\login\Feature
 */
trait Signup
{

    function initSignup()
    {
        $this->addAction('register_new_user', ['fields' => ['login', 'email', 'password']]);
    }

    function register_new_user()
    {
        $this->save();
    }

}