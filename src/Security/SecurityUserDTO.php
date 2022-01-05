<?php

namespace App\Security;

use App\Validator as CrozonAssert;
use Symfony\Component\Validator\Constraints as Assert;

final class SecurityUserDTO
{
    #[CrozonAssert\UniqueUser]
    public string $email;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 32)]
    #[Assert\NotCompromisedPassword]
    public string $password;
}
