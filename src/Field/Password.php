<?php

declare(strict_types=1);

namespace Atk4\Login\Field;

use Atk4\Core\InitializerTrait;
use Atk4\Data\Exception;
use Atk4\Data\Field;
use Atk4\Data\Persistence;
use Atk4\Ui\Persistence\UI;

class Password extends Field
{
    use InitializerTrait {
        init as _init;
    }

    /** @var string data field type */
    public $type = 'password';

    /**
     * Keeping the actual hash protected, in case we have to validate password with
     * compare().
     *
     * @var string
     */
    protected $passwordHash;

    /**
     * Optional callable for encrypting password.
     * Use it if you need to customize your password encryption algorithm.
     * Receives parameters - plaintext password.
     *
     * @var callable
     */
    public $encryptMethod;

    /**
     * Optional callable for verifying password.
     * Use it if you need to customize your password verification algorithm.
     * Receives parameters - plaintext password, encrypted password.
     *
     * @var callable
     */
    public $verifyMethod;

    /**
     * Initialization.
     */
    protected function init(): void
    {
        $this->_init();
        $this->setDefaultTypecastMethods();
    }

    /**
     * Cloning function.
     */
    public function __clone()
    {
        // IMPORTANT: This is required as workaround in case you clone model.
        // Otherwise it will use encrypt/decrypt method of old model object.
        $this->setDefaultTypecastMethods();
    }

    /**
     * Sets default typecast methods.
     */
    protected function setDefaultTypecastMethods()
    {
        $this->typecast = [
            // callback on saving
            function (?string $password, Field $f, Persistence $p) {
                return $this->encrypt($password, $f, $p);
            },
            // callback on loading
            function (?string $password, Field $f, Persistence $p) {
                return $this->decrypt($password, $f, $p);
            },
        ];
    }

    /**
     * Normalize password - remove hash.
     *
     * @param string $value password
     *
     * @return mixed
     */
    public function normalize($value)
    {
        $this->passwordHash = null;

        return parent::normalize($value);
    }

    /**
     * DO NOT CALL THIS METHOD. It is automatically invoked when you save
     * your model.
     *
     * When storing password to persistence, it will be encrypted. We will
     * also update $this->passwordHash, in case you'll want to perform
     * verify right after.
     *
     * @param string $password plaintext password
     *
     * @return string|null encrypted password
     */
    public function encrypt(?string $password, Field $f, Persistence $p)
    {
        if ($password === null) {
            return null;
        }

        // encrypt password
        if (is_callable($this->encryptMethod)) {
            $this->passwordHash = call_user_func_array($this->encryptMethod, [$password]);
        } else {
            $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
        }

        return $this->passwordHash;
    }

    /**
     * DO NOT CALL THIS METHOD. It is automatically invoked when you load
     * your model.
     *
     * @param string $password encrypted password
     *
     * @return string|null encrypted password
     */
    public function decrypt(?string $password, Field $f, Persistence $p)
    {
        $this->passwordHash = $password;
        if ($p instanceof UI) {
            return $password;
        }

        return null;
    }

    /**
     * Verify if the password user have supplied you with is correct.
     *
     * @param string $password plain text password
     *
     * @return bool true if passwords match
     */
    public function verify($password): bool
    {
        if ($this->passwordHash === null) {
            // perhaps we currently hold a password and it's not saved yet.
            $v = $this->get();

            if ($v) {
                return $v === $password;
            }

            throw (new Exception('Password was not set, so verification is not possible'))
                ->addMoreInfo('field', $this->name);
        }

        // verify password
        $v = is_callable($this->verifyMethod)
                ? call_user_func_array($this->verifyMethod, [$password, $this->passwordHash])
                : password_verify($password, $this->passwordHash);

        return $v;
    }

    /**
     * Randomly generate a password, that is easy to memorize. There are
     * 116985856 unique password combinations with length of 4.
     *
     * To make this more complex, use suggestPasssword(3).' '.suggestPassword(3);
     */
    public function suggestPassword(int $length = 4, int $words = 1): string
    {
        $p5 = ['', 'k', 's', 't', 'n', 'h', 'm', 'r', 'w', 'g', 'z', 'd', 'b', 'p'];
        $p3 = ['y', 'ky', 'sh', 'ch', 'ny', 'my', 'ry', 'gy', 'j', 'py', 'by'];
        $a5 = ['a', 'i', 'u', 'e', 'o'];
        $a3 = ['a', 'u', 'o'];
        $syl = ['n'];

        foreach ($p5 as $p) {
            foreach ($a5 as $a) {
                $syl[] = $p . $a;
            }
        }

        foreach ($p3 as $p) {
            foreach ($a3 as $a) {
                $syl[] = $p . $a;
            }
        }

        $pass = '';

        for ($i = 0; $i < $length; ++$i) {
            $pass .= $syl[array_rand($syl)];
        }

        return $pass;
    }
}
