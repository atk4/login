<?php

declare(strict_types=1);

namespace Atk4\Login\Tests\Feature;

use Atk4\Login\Model\User;
use Atk4\Login\Tests\GenericTestCase;

class SendEmailActionTest extends GenericTestCase
{
    public function testBasic(): void
    {
        $this->setupDefaultDb();
        $m = $this->createUserModel();

        self::assertTrue($m->hasUserAction('sendEmail'));

        $entity = $m->load(1);

        // replace callback so we can catch it
        $entity->getUserAction('sendEmail')->callback = static function () {
            $args = func_get_args();
            static::assertInstanceOf(User::class, $args[0]);
            static::assertSame('Email subject', $args[1]);
            static::assertSame('Email body', $args[2]);
        };

        $entity->executeUserAction('sendEmail', 'Email subject', 'Email body');
    }
}
