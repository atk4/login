<?php

declare(strict_types=1);

namespace Atk4\Login\Field;

use Atk4\Data\Exception;
use Atk4\Data\Field;

class Password extends Field
{
    /**
     * DO NOT CALL THIS METHOD. Password is always hashed immediately when set/normalized..
     */
    protected function hashPasswordIsHashed(string $value): bool
    {
        $info = password_get_info($value);

        return $info['algo'] !== null;
    }

    /**
     * DO NOT CALL THIS METHOD. Password is always hashed immediately when set/normalized..
     */
    protected function hashPassword(string $password): string
    {
        return password_hash($password, \PASSWORD_DEFAULT);
    }

    /**
     * DO NOT CALL THIS METHOD. Use verifyPassword method instead.
     */
    protected function hashPasswordVerify(string $hash, string $password): bool
    {
        return password_verify($password, $hash);
    }

    public function normalize($value)
    {
        $value = parent::normalize($value);
        if ($value !== null && !$this->hashPasswordIsHashed($value)) {
            $oldValue = $this->get();
            if ($oldValue !== null && $this->verifyPassword($value)) { // do not rehash if the old password is the same
                $value = $oldValue;
            } else {
                $value = $this->hashPassword($value);
            }
        }

        return $value;
    }

    /**
     * Returns true if the supplied password matches the stored hash.
     */
    public function verifyPassword(string $password): bool
    {
        if ($this->get() === null || $this->get() === '') {
            throw (new Exception('Password was not set, verification is not possible'))
                ->addMoreInfo('field', $this->name);
        }

        return $this->hashPasswordVerify($this->get(), $password);
    }

    /**
     * Randomly generate a password, that is easy to memorize. There are
     * 116985856 unique password combinations with length of 4.
     */
    public function suggestPassword(int $length = 8): string
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
