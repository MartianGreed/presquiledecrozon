<?php

namespace App\Entity\Data;

use App\Entity\Identity;
use App\Entity\IdentityTrait;
use App\Repository\Data\TownRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TownRepository::class)]
class Town implements \Stringable, Identity
{
    use IdentityTrait;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $inseeCode = null;

    #[ORM\ManyToOne(targetEntity: PostalCode::class, inversedBy: 'towns')]
    private PostalCode $postalCode;

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getInseeCode(): ?string
    {
        return $this->inseeCode;
    }

    public function setInseeCode(string $inseeCode): self
    {
        $this->inseeCode = $inseeCode;

        return $this;
    }

    public function getPostalCode(): PostalCode
    {
        return $this->postalCode;
    }

    public function setPostalCode(PostalCode $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }
}
