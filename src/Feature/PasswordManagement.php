<?php

declare(strict_types=1);

namespace Atk4\Login\Feature;

use Atk4\Data\Model\UserAction;
use Atk4\Login\Field\Password;

/**
 * Enables your User model to perform various actions with the passwords.
 */
trait PasswordManagement
{
    /**
     * This must be consistent with config.yaml.
     */
    public function initPasswordManagement()
    {
        $this->addUserAction('generate_random_password', [
            'appliesTo' => UserAction::APPLIES_TO_NO_RECORDS,
            'system' => true,
            'args' => [
                'length' => ['type' => 'int'],
                'words' => ['type' => 'int'],
            ],
        ]);
        $this->addUserAction('reset_password', [
            'appliesTo' => UserAction::APPLIES_TO_SINGLE_RECORD,
            'args' => [
                'length' => ['type' => 'int'],
                'words' => ['type' => 'int'],
            ],
        ]);
        $this->addUserAction('check_password_strength', [
            // 'appliesTo' => UserAction::APPLIES_TO_NO_RECORDS,
            'args' => [
                'password' => ['type' => 'string'],
            ],
        ]);
    }

    /**
     * Generate random password for the user, returns it.
     */
    public function generate_random_password(int $length = 4, int $words = 1): string
    {
        return (new Password())->suggestPassword($length, $words);
    }

    /**
     * Assumes that current model has 'password' and 'email' fields. Will set random password
     * and then will email user about it (if $app->outbox is set up correctly).
     *
     * @todo This method relies on default password field name and will not work
     *       if different fieldPassword are set in Auth controller.
     *       This has to be fixed somehow.
     */
    public function reset_password(int $length = null, int $words = null): string
    {
        $fieldPassword = 'password';

        $password = $this->generate_random_password(
            $length ?: 4,
            $words ?: 1
        );

        $this->save([$fieldPassword => $password]);

        // if we have SendEmailAction in this model, then send new password by email
        if ($this->hasUserAction('sendEmail')) {
            $this->executeUserAction('sendEmail', 'Password reset', 'New password: ' . $password);
        }

        return $password;
    }

    /**
     * Will verify password against several verification mechanisms, returns suggestion.
     * Can be used in validation.
     *
     *  - strength=5: uses scale of 1 to 10 to measure how strong password is (see https://howsecureismypassword.net)
     *  https://gist.github.com/xrstf/2926619
     *
     * Generally specifying good password strength requirement will ensure that the password is difficult to
     * guess / crack.
     *
     *  - symbols: 0 (minimum number of symbols)
     *  - numbers: 0 (minimum number of numbers)
     *  - len: 0 (minimum length)
     *  - upper: 0 (minimum upper characters)
     *
     * @return string|null
     */
    public function check_password_strength(string $password, array $settings = ['strength' => 3])
    {
        $length = strlen($password);
        $nUpper = 0;
        $nLower = 0;
        $nNum = 0;
        $nSymbol = 0;
        for ($i = 0; $i < $length; ++$i) {
            $ch = $password[$i];
            $code = ord($ch);
            /* [0-9] */ if ($code >= 48 && $code <= 57) {
                ++$nNum;
            }
            /* [A-Z] */ elseif ($code >= 65 && $code <= 90) {
                ++$nUpper;
            }
            /* [a-z] */ elseif ($code >= 97 && $code <= 122) {
                ++$nLower;
            }
            /* .     */ else {
                ++$nSymbol;
            }
        }

        $strength = intdiv($this->calculate_strength($password), 10);

        if (isset($settings['strength']) && $strength < $settings['strength']) {
            return 'Password is not strong enough. Make it longer or use more capitals and symbols.';
        }

        if (isset($settings['len']) && $length < $settings['len']) {
            return 'Password should be at least ' . $settings['len'] . ' characters long';
        }

        if (isset($settings['symbols']) && $nSymbol < $settings['symbols']) {
            return 'Password requires at least ' . $settings['symbols'] . ' symbols';
        }

        if (isset($settings['numbers']) && $nNum < $settings['numbers']) {
            return 'Password requires at least ' . $settings['numbers'] . ' numbers';
        }

        if (isset($settings['upper']) && $nUpper < $settings['upper']) {
            return 'Password requires at least ' . $settings['upper'] . ' uppercase characters';
        }

        return null;
    }

    /**
     * Calculate score for a password. Credit: https://gist.github.com/xrstf/2926619.
     */
    private function calculate_strength(string $pw): int
    {
        $length = strlen($pw);
        $score = $length * 4;
        $nUpper = 0;
        $nLower = 0;
        $nNum = 0;
        $nSymbol = 0;
        $locUpper = [];
        $locLower = [];
        $locNum = [];
        $locSymbol = [];
        $charDict = [];
        // count character classes
        for ($i = 0; $i < $length; ++$i) {
            $ch = $pw[$i];
            $code = ord($ch);
            /* [0-9] */ if ($code >= 48 && $code <= 57) {
                ++$nNum;
                $locNum[] = $i;
            }
            /* [A-Z] */ elseif ($code >= 65 && $code <= 90) {
                ++$nUpper;
                $locUpper[] = $i;
            }
            /* [a-z] */ elseif ($code >= 97 && $code <= 122) {
                ++$nLower;
                $locLower[] = $i;
            }
            /* .     */ else {
                ++$nSymbol;
                $locSymbol[] = $i;
            }
            if (!isset($charDict[$ch])) {
                $charDict[$ch] = 1;
            } else {
                ++$charDict[$ch];
            }
        }
        // reward upper/lower characters if pw is not made up of only either one
        if ($nUpper !== $length && $nLower !== $length) {
            if ($nUpper !== 0) {
                $score += ($length - $nUpper) * 2;
            }
            if ($nLower !== 0) {
                $score += ($length - $nLower) * 2;
            }
        }
        // reward numbers if pw is not made up of only numbers
        if ($nNum !== $length) {
            $score += $nNum * 4;
        }
        // reward symbols
        $score += $nSymbol * 6;
        // middle number or symbol
        foreach ([$locNum, $locSymbol] as $list) {
            $reward = 0;
            foreach ($list as $i) {
                $reward += ($i !== 0 && $i !== $length - 1) ? 1 : 0;
            }
            $score += $reward * 2;
        }
        // chars only
        if ($nUpper + $nLower === $length) {
            $score -= $length;
        }
        // numbers only
        if ($nNum === $length) {
            $score -= $length;
        }
        // repeating chars
        $repeats = 0;
        foreach ($charDict as $count) {
            if ($count > 1) {
                $repeats += $count - 1;
            }
        }
        if ($repeats > 0) {
            $score -= (int) (floor($repeats / ($length - $repeats)) + 1);
        }
        if ($length > 2) {
            // consecutive letters and numbers
            foreach (['/[a-z]{2,}/', '/[A-Z]{2,}/', '/[0-9]{2,}/'] as $re) {
                preg_match_all($re, $pw, $matches, PREG_SET_ORDER);
                if (!empty($matches)) {
                    foreach ($matches as $match) {
                        $score -= (strlen($match[0]) - 1) * 2;
                    }
                }
            }
            // sequential letters
            $locLetters = array_merge($locUpper, $locLower);
            sort($locLetters);
            foreach ($this->findSequence($locLetters, mb_strtolower($pw)) as $seq) {
                if (count($seq) > 2) {
                    $score -= (count($seq) - 2) * 2;
                }
            }
            // sequential numbers
            foreach ($this->findSequence($locNum, mb_strtolower($pw)) as $seq) {
                if (count($seq) > 2) {
                    $score -= (count($seq) - 2) * 2;
                }
            }
        }

        return $score;
    }

    /**
     * Find all sequential chars in string $src.
     *
     * Only chars in $charLocs are considered. $charLocs is a list of numbers.
     * For example if $charLocs is [0,2,3], then only $src[2:3] is a possible
     * substring with sequential chars.
     *
     * @return array [[c,c,c,c], [a,a,a], ...]
     */
    private function findSequence(array $charLocs, string $src): array
    {
        $sequences = [];
        $sequence = [];
        for ($i = 0; $i < count($charLocs) - 1; ++$i) {
            $here = $charLocs[$i];
            $next = $charLocs[$i + 1];
            $charHere = $src[$charLocs[$i]];
            $charNext = $src[$charLocs[$i + 1]];
            $distance = $next - $here;
            $charDistance = ord($charNext) - ord($charHere);
            if ($distance === 1 && $charDistance === 1) {
                // We find a pair of sequential chars!
                if (empty($sequence)) {
                    $sequence = [$charHere, $charNext];
                } else {
                    $sequence[] = $charNext;
                }
            } elseif (!empty($sequence)) {
                $sequences[] = $sequence;
                $sequence = [];
            }
        }
        if (!empty($sequence)) {
            $sequences[] = $sequence;
        }

        return $sequences;
    }
}
