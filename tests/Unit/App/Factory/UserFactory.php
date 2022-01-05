<?php

namespace App\Tests\Unit\App\Factory;

use App\Entity\User;

final class UserFactory
{
    public static function createUser(): User
    {
        $user = new User();

        $user->setEmail('valentin.dosimont@gmail.com')->setPassword('encrypt3dP4ssw0rd');

        return $user;
    }
}
