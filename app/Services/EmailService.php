<?php

namespace App\Services;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class EmailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(
        string $recipient,
        string $subject,
        string $message,
    ): bool {
        $email = new Email()
            ->from(
                new Address(
                    $GLOBALS["cfg"]["mail"]["from_email"],
                    $GLOBALS["cfg"]["mail"]["from_name"],
                ),
            )
            ->to($recipient)
            ->subject($subject)
            ->text(strip_tags($message))
            ->html($message);

        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }
}
