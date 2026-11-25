<?php

namespace App\Services;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Envia correus electrònics.
 */
class EmailService
{
    private MailerInterface $mailer;

    /**
     * @param MailerInterface $mailer Servei de transport de correu.
     */
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Envia un correu en format text i HTML.
     * @return bool Indica si s'ha pogut enviar.
     */
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
