<?php

namespace App\Entity\Rental;

use App\Entity\Data\Bed;
use App\Entity\Identity;
use App\Entity\IdentityTrait;
use App\Repository\Rental\BedroomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BedroomRepository::class)]
class Bedroom implements \Stringable, Identity
{
    use IdentityTrait;

    #[ORM\ManyToOne(targetEntity: Configuration::class, inversedBy: 'bedrooms')]
    #[ORM\JoinColumn(nullable: false)]
    private Configuration $configuration;

    /**
     * @var ArrayCollection<int, BedroomBed>
     */
    #[ORM\OneToMany(targetEntity: BedroomBed::class, mappedBy: 'bedroom', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Collection $beds;

    public function __construct()
    {
        $this->beds = new ArrayCollection();
    }

    public function __toString(): string
    {
        return implode(', ', $this->beds->map(fn (BedroomBed $bedroomBed) => $bedroomBed->getBed()->getLabel())->toArray());
    }

    /**
     * @psalm-return ArrayCollection<int, BedroomBed>
     */
    public function getBeds(): Collection
    {
        return $this->beds;
    }

    public function addBed(Bed $bed, int $count): self
    {
        $this->beds[] = BedroomBed::new($this, $bed, $count);

        return $this;
    }

    public function removeBed(Bed $bed): self
    {
        $bedroomBed = $this->beds->filter(fn (BedroomBed $bb) => $bb->getBed() === $bed)->first();
        if ($bedroomBed instanceof BedroomBed) {
            $this->beds->removeElement($bedroomBed);
        }

        return $this;
    }

    public function clearBedroom(): void
    {
        foreach ($this->beds as $bed) {
            $bed->setBedroom(null);
        }

        $this->beds = new ArrayCollection();
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
