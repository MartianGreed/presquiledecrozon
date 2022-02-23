<?php

namespace App\Controller\Profile;

use App\Controller\WithUserTrait;
use App\Form\SendMessageType;
use App\Repository\Conversation\ConversationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class GetMessagesListController extends AbstractController
{
    use WithUserTrait;

    public function __construct(
        private readonly ConversationRepository $conversationRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    )
    {
    }

    #[Route('/mon-compte/messages', name: 'app_profile_conversation')]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(SendMessageType::class, null, [
            'method' => Request::METHOD_POST,
            'action' => $this->urlGenerator->generate('api_post_message'),
        ]);


        return $this->renderForm('profile/get_messages_list.html.twig', [
            'conversations' => $this->conversationRepository->getUserConversations($this->getUser()->getId()),
            'form' => $form,
        ]);
    }
}