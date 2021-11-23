<?php

declare(strict_types=1);

namespace Atk4\Login\Feature;

use Atk4\Data\Model\UserAction;

/**
 * Adding this trait to your user model will allow users to send emails. Additionally execute
 * $this->initSendEmailAction() from your init() method.
 */
trait SendEmailActionTrait
{
    /**
     * Adds sendEmail action.
     */
    public function initSendEmailAction(): UserAction
    {
        return $this->addUserAction('sendEmail', [
            'callback' => 'sendEmail',
            'appliesTo' => UserAction::APPLIES_TO_SINGLE_RECORD,
            'caption' => 'Email',
            'description' => 'Send e-mail to user',
            'args' => [
                'subject' => ['caption' => 'Subject', 'type' => 'string'],
                'message' => ['caption' => 'Message', 'type' => 'text'],
            ],
        ]);
    }

    /**
     * Sends email.
     *
     * @param string $subject Email subject
     * @param string $message Email body
     */
    public function sendEmail(string $subject, string $message): bool
    {
        $to = $this->get('email');
        $message = str_replace(["\r\n", "\r", "\n"], \PHP_EOL, $message);
        $message = wordwrap($message, 70, \PHP_EOL);

        return mail($to, $subject, $message);
    }
}
