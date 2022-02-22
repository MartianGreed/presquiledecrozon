<?php

namespace App\Controller\Profile;

use App\Controller\WithUserTrait;
use App\Repository\Conversation\ConversationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GetMessagesListController extends AbstractController
{
    use WithUserTrait;

    public function __construct(private readonly ConversationRepository $conversationRepository)
    {
    }

    #[Route('/mon-compte/messages', name: 'app_profile_conversation')]
    public function __invoke(): Response
    {
        return $this->render('profile/get_messages_list.html.twig', [
            'conversations' => $this->conversationRepository->getUserConversations($this->getUser()->getId()),
        ]);
    }
}