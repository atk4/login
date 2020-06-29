<?php

declare(strict_types=1);

namespace atk4\login\Feature;

use atk4\data\Model\UserAction;

/**
 * Adding this trait to your user model will allow users to send emails. Additionally execute
 * $this->initSendEmailAction() from your init() method.
 */
trait SendEmailAction
{
    /**
     * Adds sendEmail action.
     */
    public function initSendEmailAction(): UserAction\Generic
    {
        return $this->addUserAction('sendEmail', [
            'callback' => 'sendEmail',
            'appliesTo' => UserAction::APPLIES_TO_SINGLE_RECORD,
            'description' => 'Send e-mail to user',
            'ui' => ['icon' => 'mail'],
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
        $message = str_replace(["\r\n", "\r", "\n"], PHP_EOL, $message);
        $message = wordwrap($message, 70, PHP_EOL);

        return mail($to, $subject, $message);
    }
}
