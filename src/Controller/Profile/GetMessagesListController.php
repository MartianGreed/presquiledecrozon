<?php

namespace App\Controller\Profile;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GetMessagesListController extends AbstractController
{
    #[Route('/mon-compte/messages', name: 'app_profile_conversation')]
    public function __invoke(): Response
    {
        return $this->render('profile/get_messages_list.html.twig');
    }
}