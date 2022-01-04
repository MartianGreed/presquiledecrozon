<?php

namespace App\Entity\Rental;

use App\Entity\IdentityTrait;
use App\Repository\Rental\TaxRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaxRepository::class)]
class Tax
{
    use IdentityTrait;

    #[ORM\Column(type: 'string', length: 20)]
    private $localTax;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private ?int $cleaningTax;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $linensTax = null;

    #[ORM\OneToOne(mappedBy: 'tax', targetEntity: Rental::class)]
    private Rental $rental;

    public function getLocalTax(): ?string
    {
        return $this->localTax;
    }

    public function setLocalTax(string $localTax): self
    {
        $this->localTax = $localTax;

        return $this;
    }

    public function getCleaningTax(): ?int
    {
        return $this->cleaningTax;
    }

    public function setCleaningTax(int $cleaningTax): self
    {
        $this->cleaningTax = $cleaningTax;

        return $this;
    }

    public function getLinensTax(): ?int
    {
        return $this->linensTax;
    }

    public function setLinensTax(int $linensTax): self
    {
        $this->linensTax = $linensTax;

        return $this;
    }

    public function getRental(): ?Rental
    {
        return $this->rental;
    }

    public function setRental(?Rental $rental): self
    {
        // unset the owning side of the relation if necessary
        if ($rental === null && $this->rental !== null) {
            $this->rental->setTax(null);
        }

        // set the owning side of the relation if necessary
        if ($rental !== null && $rental->getTax() !== $this) {
            $rental->setTax($this);
        }

        $this->rental = $rental;

        return $this;
    }
}
