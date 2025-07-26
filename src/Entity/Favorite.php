<?php

namespace App\Entity;

use App\Entity\Rental\Rental;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: FavoriteRepository::class)]
class Favorite
{
    use IdentityTrait;
    use TimestampabbleTrait;

    #[ORM\ManyToOne(targetEntity: Rental::class)]
    #[ORM\JoinColumn(nullable: false, unique: true)]
    private Rental $rental;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private User $persona;

    public function getRental(): ?Rental
    {
        return $this->rental;
    }

    public function setRental(Rental $rental): self
    {
        $this->rental = $rental;

        return $this;
    }

    public function getPersona(): ?User
    {
        return $this->persona;
    }

    public function setPersona(User $persona): self
    {
        $this->persona = $persona;

        return $this;
    }
}
