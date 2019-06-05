<?php


namespace atk4\login;


/**
 * Enables your User model to perform various actions with the passwords
 *
 * @package atk4\login
 */
trait PasswordManagement
{

    function initPasswordManagement()
    {
        $this->addAction('generate_random_password', ['scope'=>'no_records', 'system'=>true]);
        $this->addAction('reset_password', ['scope'=>'single_record']);

        $this->app->addon['atk4/login']['defaultPasswordLength'];
    }

    /**
     * Generate random password for the user, returns it.
     */
    function generate_random_password($length = 4, $words = 1)
    {
        $p5 = ['','k','s','t','n','h','m','r','w','g','z','d','b','p'];
        $p3 = ['y','ky','sh','ch','ny','my','ry','gy','j','py','by'];
        $a5 = ['a','i','u','e','o'];
        $a3 = ['a','u','o'];
        $syl=['n'];

        foreach($p5 as $p) {
            foreach($a5 as $a) {
                $syl[] = $p.$a;
            }
        }

        foreach($p3 as $p) {
            foreach($a3 as $a) {
                $syl[] = $p.$a;
            }
        }

        $pass = '';

        for ($i = 0; $i < $length; $i++) {
            $pass .= $syl[array_rand($syl)];
        }

        return $pass;
    }


    /**
     * Assumes that current model has 'password' and 'email' fields. Will set random password
     * and then will email user about it (if $app->outbox is set up correctly)
     */
    function reset_password($length = null, $words = null)
    {
        $password = $this->generate_random_password(
            $length ?: $this->app->addonConfig['atk4/login']['defaultPasswordLength'] ?? 4,
            $words
        );

        $this['password'] = $password;
        $this->save();

        if ($this->hasField('email') && $this->app->outbox) {
            $this->app->outbox->sendEmail($this['email'], 'password_reset', ['new_password'=>$password]);
            return 'Password was emailed to '.$this['email'];
        }

        return $password;
    }

}