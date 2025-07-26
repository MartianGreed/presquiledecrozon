<?php

declare(strict_types=1);

namespace App\Mailer;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

final class RequestResetPasswordMailer
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly MailerInterface $mailer,
        private readonly string $emailSender,
    ) {
    }

    public function send(User $user): void
    {
        // Email owner to confirm rental has been published
        $email = (new TemplatedEmail())
            ->from($this->emailSender)
            ->to(new Address((string) $user->getEmail()))
            ->subject('Votre demande de réinitialisation de mot de passe')
            ->htmlTemplate('emails/request_reset_password.html.twig')
            ->context([
                'profile' => $user->getProfile(),
                'reset_password_url' => $this->router->generate('app_reset_password', ['token' => $user->getResetToken()], UrlGeneratorInterface::ABSOLUTE_URL),
            ])
        ;

        $this->mailer->send($email);
    }
}
