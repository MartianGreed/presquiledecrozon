<?php

namespace App\Infrastructure\Symfony\Security;

use Symfony\Component\Validator\Constraints as Assert;

final class SecurityUserDTO
{
    #[\App\Infrastructure\Symfony\Validator\UniqueUser]
    public string $email;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 32)]
    #[Assert\NotCompromisedPassword]
    public string $password;
}
