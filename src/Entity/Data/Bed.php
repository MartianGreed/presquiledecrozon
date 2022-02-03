<?php

namespace App\Entity\Data;

use App\Domain\Rental\BedSize;
use App\Entity\IdentityTrait;
use App\Repository\Data\BedRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: BedRepository::class)]
class Bed
{
    use IdentityTrait;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $label = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $help = null;

    #[ORM\Column(type: 'bed_size', nullable: true)]
    private ?BedSize $size = null;

    public function __toString(): string
    {
        return $this->label ?? '';
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function setHelp(?string $help): self
    {
        $this->help = $help;

        return $this;
    }

    /** @psalm-return array<string, int>|null */
    public function getSize(): ?array
    {
        return $this->size?->toArray();
    }

    public function setSize(?BedSize $size): self
    {
        $this->size = $size;

        return $this;
    }
}
