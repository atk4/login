<?php

// vim:ts=4:sw=4:et:fdm=marker:fdl=0

namespace atk4\login\Field;

use atk4\core\InitializerTrait;

class Password extends \atk4\data\Field
{
    use InitializerTrait {
        init as _init;
    }

    public $type = 'password';

    /**
     * Keeping the actual hash protected, in case we have to validate password with
     * compare().
     *
     * @var string
     */
    public $password_hash = null;

    public function init()
    {
        $this->_init();

        $this->typecast = [
            [$this, 'encrypt'],
            function ($v, $f, $p) {
                $this->password_hash = $v;
                if ($p instanceof \atk4\ui\Persistence\UI) {
                    return $v;
                }
                return null;
            },
        ];
    }

    public function normalize($value)
    {
        $this->password_hash = null;

        return parent::normalize($value);
    }

    /**
     * DO NOT CALL THIS METHOD. It is automatically invoked when you save
     * your model.
     *
     * When storing password to persistance, it will be encrypted. We will
     * also update $this->password_hash, in case you'll want to perform
     * verify right after.
     *
     * @param string $password plaintext password
     *
     * @return string encrypted password
     */
    public function encrypt($password)
    {
        if (is_null($password)) {
            return;
        }

        $this->password_hash = password_hash($password, PASSWORD_DEFAULT);

        return $this->password_hash;
    }

    /**
     * Verify if the password user have suppplied you with is correct.
     *
     * @param string $password plain text password
     *
     * @return bool true if passwords match
     */
    public function compare($password)
    {
        if (is_null($this->password_hash)) {

            // perhaps we currently hold a password and it's not saved yet.
            $v = $this->get();

            if ($v) {
                return $v === $password;
            }

            throw new \atk4\data\Exception(['Password was not set, so verification is not possible', 'field'=>$this->name]);
        }

        return password_verify($password, $this->password_hash);
    }

    /**
     * Randomly generate a password, that is easy to memorize. There are
     * 116985856 unique password combinations with length of 4.
     *
     * To make this more complex, use suggestPasssword(3).' '.suggestPassword(3);
     *
     * @return string
     */
    public function suggestPassword($length = 4, $words = 1)
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

        for ($i=0; $i<$length; $i++) {
            $pass.=$syl[array_rand($syl)];
        }

        return $pass;
    }
}
