<?php

namespace App\Entity\Rental;

use App\Domain\Price;
use App\Entity\Data\Linens;
use App\Entity\Identity;
use App\Entity\IdentityTrait;
use App\Repository\Rental\TaxRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaxRepository::class)]
class Tax implements Identity
{
    use IdentityTrait;

    #[ORM\Column(type: 'string', length: 20)]
    private string $localTax;

    #[ORM\Column(type: 'price', options: [
        'default' => 0,
    ])]
    private ?Price $cleaningTax = null;

    #[ORM\Column(type: 'price', nullable: true)]
    private ?Price $linensTax = null;

    /**
     * @var ArrayCollection<int, Linens>
     */
    #[ORM\ManyToMany(targetEntity: Linens::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'tax_id')]
    private Collection $linens;

    #[ORM\OneToOne(mappedBy: 'tax', targetEntity: Rental::class)]
    private Rental $rental;

    public function __construct()
    {
        $this->linens = new ArrayCollection();
    }

    public function getLocalTax(): ?string
    {
        return $this->localTax;
    }

    public function setLocalTax(string $localTax): self
    {
        $this->localTax = $localTax;

        return $this;
    }

    public function getCleaningTax(): ?Price
    {
        return $this->cleaningTax;
    }

    public function setCleaningTax(Price $cleaningTax): self
    {
        $this->cleaningTax = $cleaningTax;

        return $this;
    }

    public function getLinensTax(): ?Price
    {
        return $this->linensTax;
    }

    public function setLinensTax(Price $linensTax): self
    {
        $this->linensTax = $linensTax;

        return $this;
    }

    public function addLinen(Linens $linen): self
    {
        if (! $this->linens->contains($linen)) {
            $this->linens[] = $linen;
        }

        return $this;
    }

    public function removeLinen(Linens $linens): self
    {
        if ($this->linens->contains($linens)) {
            $this->linens->removeElement($linens);
        }

        return $this;
    }

    /**
     * @return ArrayCollection<int, Linens>
     */
    public function getLinens(): Collection
    {
        return $this->linens;
    }

    public function getRental(): Rental
    {
        return $this->rental;
    }

    public function setRental(Rental $rental): self
    {
        // set the owning side of the relation if necessary
        if ($rental->getTax() !== $this) {
            $rental->setTax($this);
        }

        $this->rental = $rental;

        return $this;
    }
}
