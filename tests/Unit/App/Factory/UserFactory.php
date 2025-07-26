<?php

namespace App\Tests\Unit\App\Factory;

use App\Entity\Profile;
use App\Entity\User;

final class UserFactory
{
    public static function createUser(
        string $email = 'valentin@pupucecorp.com',
        string $password = 'encrypt3dP4ssw0rd',
        string $firstname = 'Valentin',
        string $lastname = 'Dosimont',
        string $phoneNumber = '0782848227',
    ): User {
        $user = new User();
        $profile = (new Profile())->setFirstname($firstname)->setLastname($lastname)->setCellphone($phoneNumber);

        $user->setEmail($email)->setPassword($password)->setProfile($profile);

        return $user;
    }
}
