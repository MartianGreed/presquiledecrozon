<?php

namespace App\Controller\Conversation;

use App\Controller\WithUserTrait;
use App\Domain\Exception\ConversationNotFoundException;
use App\Entity\Conversation\Message;
use App\Form\SendMessageType;
use App\Repository\Conversation\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class PostMessageController extends AbstractController
{
    use WithUserTrait;

    public function __construct(
        private readonly ConversationRepository $conversationRepository,
        private readonly EntityManagerInterface $manager,
    )
    {

    }
    #[Route('/api/conversation/message', name: 'api_post_message', methods: [Request::METHOD_POST])]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(SendMessageType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $conversationId = strval($form->get('conversation_id')->getData());
                $messageContent = strval($form->get('message')->getData());

                $conversation = $this->conversationRepository->getConversationDetails($conversationId);

                $message = Message::create($this->getUser(), $messageContent);
                $conversation->addMessage($message);

                $this->manager->persist($message);
                $this->manager->flush();

                return new JsonResponse([
                    'message' => $message->getMessage(),
                    'read_at' => $message->getReadAt()?->getTimestamp(),
                    'send_at' => $message->getSendAt()?->getTimestamp(),
                    'sender_id' => $message->getSender()?->getId(),
                ], Response::HTTP_CREATED);
            } catch (ConversationNotFoundException $e) {
                return new JsonResponse(['message' => 'invalid conversation'], Response::HTTP_NOT_FOUND);
            }
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}