<?php

namespace App\Domain\Subscription;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateSubscriptionDTO
{
    #[Assert\NotBlank]
    public string $firstname;
    #[Assert\NotBlank]
    public string $lastname;
    #[Assert\NotBlank]
    public string $civility;
    #[Assert\Email]
    public string $email;
    #[Assert\NotBlank]
    public string $phoneNumber;

    #[Assert\NotBlank]
    public string $address;
    public ?string $address2 = null;
    #[Assert\NotBlank]
    public string $town;
    #[Assert\NotBlank]
    public string $postalCode;
}
