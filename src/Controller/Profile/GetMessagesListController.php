<?php

namespace App\Controller\Profile;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class GetMessagesListController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('profile/get_messages_list.html.twig');
    }
}