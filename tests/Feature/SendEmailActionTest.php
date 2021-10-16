<?php

declare(strict_types=1);

namespace Atk4\Login\Tests\Feature;

use Atk4\Login\Model\User;
use Atk4\Login\Tests\Generic;

class SendEmailActionTest extends Generic
{
    public function testBasic()
    {
        $this->setupDefaultDb();
        $m = $this->getUserModel();

        $this->assertTrue($m->hasUserAction('sendEmail'));

        $entity = $m->load(1);

        // replace callback so we can catch it
        $entity->getUserAction('sendEmail')->callback = function () {
            $args = func_get_args();
            $this->assertInstanceOf(User::class, $args[0]);
            $this->assertSame('Email subject', $args[1]);
            $this->assertSame('Email body', $args[2]);
        };

        $entity->executeUserAction(
            'sendEmail',
            'Email subject',
            'Email body'
        );
    }
}
