<?php

declare(strict_types=1);

namespace App\Service;

use App\Mailer\RequestResetPasswordMailer;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

final class RequestResetPasswordService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly ApplicationTokenGenerator $tokenGenerator,
        private readonly RequestResetPasswordMailer $mailer,
    ) {
    }

    public function resetPassword(string $email): void
    {
        $user = $this->userRepository->findOneBy(['email' => $email, 'resetToken' => null]);
        if (null === $user) {
            // User does not exists or has already requested a new password.
            return;
        }

        $user->requestResetPassword($this->tokenGenerator->generateRandomToken((string) $user->getId()));
        $this->entityManager->flush();

        $this->mailer->send($user);
    }
}
