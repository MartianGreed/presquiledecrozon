<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Index(columns: ['target_id', 'target_class'], name: 'object_identity_idx')]
class Notification
{
    use IdentityTrait;

    private function __construct(
        #[ORM\Column(type: 'string', length: 36)]
        private readonly string $targetId,
        #[ORM\Column(type: 'string')]
        private readonly string $targetClass,
        #[ORM\Column(type: 'text')]
        private readonly string $label,
        #[ORM\Column(type: 'datetime')]
        private readonly \DateTime $createdAt,
        #[ORM\Column(type: 'datetime', nullable: true)]
        private ?\DateTime $readAt = null,
    ) {}

    public static function create(Identity $targetEntity, string $label, \DateTime $createdAt): Notification
    {
        return new self(
            (string)$targetEntity->getId(),
            \get_class($targetEntity),
            $label,
            $createdAt,
        );
    }

    public function markAsRead(\DateTime $readAt): void
    {
        $this->readAt = $readAt;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getTargetId(): string
    {
        return $this->targetId;
    }

    public function getTargetClass(): string
    {
        return $this->targetClass;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getReadAt(): ?\DateTime
    {
        return $this->readAt;
    }
}