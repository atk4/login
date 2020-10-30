<?php

declare(strict_types=1);

namespace atk4\login\tests\Feature;

use atk4\data\Model;
use atk4\login\Feature\Signup;
use atk4\login\Model\User;
use atk4\login\tests\Generic;

class SendEmailActionTest extends Generic
{
    public function testBasic()
    {
        $this->setupDefaultDb();
        $m = $this->getUserModel();

        $this->assertTrue($m->hasUserAction('sendEmail'));

        $m->load(1);

        // replace callback so we can catch it
        $m->getUserAction('sendEmail')->callback = function () {
            $args = func_get_args();
            $this->assertInstanceOf(User::class, $args[0]);
            $this->assertEquals('Email subject', $args[1]);
            $this->assertEquals('Email body', $args[2]);
        };

        $m->executeUserAction(
            'sendEmail',
            'Email subject',
            'Email body'
        );
    }
}
