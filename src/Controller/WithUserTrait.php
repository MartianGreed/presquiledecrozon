<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

trait WithUserTrait
{
    final public function getUser(): User
    {
        $user = parent::getUser();

        if (null === $user) {
            throw new UnauthorizedHttpException('Unauthorized');
        }

        assert($user instanceof User);

        return $user;
    }
}
