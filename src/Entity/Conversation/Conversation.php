<?php

namespace App\Entity\Conversation;

use App\Entity\Booking\Booking;
use App\Entity\Identity;
use App\Entity\IdentityTrait;
use App\Entity\TimestampabbleTrait;
use App\Entity\User;
use App\Repository\Conversation\ConversationRepository;
use App\Util\Type;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ConversationRepository::class)]
class Conversation implements Identity
{
    use IdentityTrait;
    use TimestampabbleTrait;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $sender;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $receiver;

    /** @var ArrayCollection<int, Message> */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'conversation', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $messages;

    #[ORM\ManyToOne(targetEntity: Booking::class)]
    private Booking $booking;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public static function initWithMessage(Booking $booking, Message $firstMessage): Conversation
    {
        $sender = Type::assertNotNull($booking->getBooker());
        $receiver = Type::assertNotNull($booking->getRental()->getOwner());

        return (new self())
            ->setBooking($booking)
            ->setSender($sender)
            ->setReceiver($receiver)
            ->addMessage($firstMessage)
        ;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(User $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    public function setReceiver(User $receiver): self
    {
        $this->receiver = $receiver;

        return $this;
    }

    /** @return ArrayCollection<int, Message> */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setConversation($this);
        }

        return $this;
    }

    public function getLastMessage(): Message
    {
        $message = $this->messages->last();
        // case should never happen
        if (!$message) {
            throw new \RuntimeException('No last message');
        }

        return $message;
    }

    public function getBooking(): Booking
    {
        return $this->booking;
    }

    public function setBooking(Booking $booking): self
    {
        $this->booking = $booking;

        return $this;
    }
}
