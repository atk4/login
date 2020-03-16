<?php
namespace atk4\login\Feature;

use atk4\data\UserAction;

/**
 * Adding this trait to your user model will allow users to send emails. Additionally execute
 * $this->initSendEmailAction() from your init() method.
 *
 * @package atk4\login\Feature
 */
trait SendEmailAction
{
    /**
     * Adds sendEmail action.
     *
     * @return UserAction\Generic
     */
    public function initSendEmailAction(): UserAction\Generic
    {
        return $this->addAction('sendEmail', [
            'callback' => [$this, 'sendEmail'],
            'scope' => UserAction\Generic::SINGLE_RECORD,
            'description' => 'Send e-mail to user',
            'ui' => ['icon' => 'mail'],
            'args' => [
                'subject' => ['caption' => 'Subject', 'type' => 'string'],
                'message' => ['caption' => 'Message', 'type' => 'text'],
            ]
        );
    }

    /**
     * Sends email.
     *
     * @param string $subject Email subject
     * @param string $message Email body
     *
     * @return bool
     */
    public function sendEmail(string $subject, string $message): bool
    {
        $to = $this['email'];
        $message = str_replace(["\r\n", "\r", "\n"], PHP_EOL, $message);
        $message = wordwrap($message, 70, PHP_EOL);
    
        return mail($to, $subject, $message);
    }
}
