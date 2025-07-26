<?php

namespace App\Controller\Conversation;

use App\Controller\WithUserTrait;
use App\Domain\Exception\ConversationNotFoundException;
use App\Entity\Conversation\Message;
use App\Repository\Conversation\ConversationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GetConversationDetailsController extends AbstractController
{
    use WithUserTrait;

    public function __construct(private readonly ConversationRepository $conversationRepository)
    {
    }

    #[Route('/api/conversation/{id}', name: 'api_get_conversation', methods: [Request::METHOD_GET])]
    public function __invoke(Request $request, string $id): Response
    {
        try {
            $conversation = $this->conversationRepository->getConversationDetails($id);
        } catch (ConversationNotFoundException $e) {
            throw $this->createNotFoundException($e);
        }

        $booking = $conversation->getBooking();

        return new JsonResponse([
            'full_name' => $conversation->getSender()?->getId() === $this->getUser()->getId() ? $conversation->getReceiver()?->getProfile()?->getFullName() : $conversation->getSender()?->getProfile()?->getFullName(),
            'period' => $booking->getPeriod(),
            'people_count' => $booking->getPeopleCount(),
            'total_price' => $booking->getPrices()->getTotalPrice()->__toString(),
            'messages' => $conversation->getMessages()->map(fn (?Message $m = null) => [
                'message' => $m?->getMessage(),
                'read_at' => $m?->getReadAt()?->getTimestamp(),
                'send_at' => $m?->getSendAt()?->getTimestamp(),
                'sender_id' => $m?->getSender()?->getId()
            ])->toArray(),
        ]);
    }
}
