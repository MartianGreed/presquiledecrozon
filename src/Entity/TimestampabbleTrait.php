<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait TimestampabbleTrait
{
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;

    final public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    final public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    final public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    final public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
