<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ResetPasswordService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    public function resetPassword(string $token, string $plainPassword): void
    {
        $user = $this->userRepository->findOneBy([
            'resetToken' => $token,
        ]);
        if (null === $user) {
            return;
        }

        $user->resetPassword($this->hasher->hashPassword($user, $plainPassword));
        $this->entityManager->flush();
    }
}
