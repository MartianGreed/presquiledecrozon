<?php

namespace App\Entity\Rental;

use App\Entity\Data\Bed;
use App\Entity\IdentityTrait;
use App\Repository\Rental\BedroomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BedroomRepository::class)]
class Bedroom implements \Stringable
{
    use IdentityTrait;

    #[ORM\ManyToOne(targetEntity: Configuration::class, inversedBy: 'bedrooms')]
    #[ORM\JoinColumn(nullable: false)]
    private Configuration $configuration;

    /** @var ArrayCollection<int, Bed> */
    #[ORM\ManyToMany(targetEntity: Bed::class)]
    private Collection $beds;

    public function __construct()
    {
        $this->beds = new ArrayCollection();
    }

    public function __toString(): string
    {
        return implode(', ', $this->beds->map(fn (Bed $bed) => $bed->getLabel())->toArray());
    }

    /**
     * @psalm-return ArrayCollection<int, Bed>
     */
    public function getBeds(): Collection
    {
        return $this->beds;
    }

    public function addBed(Bed $bed): self
    {
        if (!$this->beds->contains($bed)) {
            $this->beds[] = $bed;
        }

        return $this;
    }

    public function removeBed(Bed $bed): self
    {
        $this->beds->removeElement($bed);

        return $this;
    }


    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function setConfiguration(Configuration $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }
}
