<?php

namespace App\Entity\Data;

use App\Repository\Data\RegionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: RegionRepository::class)]
class Region
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $prefix1 = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $prefix2 = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: 'boolean')]
    private bool $displayOldName = false;

    #[ORM\ManyToOne(targetEntity: Country::class, inversedBy: 'regions')]
    #[ORM\JoinColumn(nullable: false)]
    private Country $country;

    /** @var ArrayCollection<int, Department> */
    #[ORM\OneToMany(mappedBy: 'region', targetEntity: Department::class)]
    private Collection $departments;

    public function __construct()
    {
        $this->departments = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
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

    public function getPrefix1(): ?string
    {
        return $this->prefix1;
    }

    public function setPrefix1(string $prefix1): self
    {
        $this->prefix1 = $prefix1;

        return $this;
    }

    public function getPrefix2(): ?string
    {
        return $this->prefix2;
    }

    public function setPrefix2(string $prefix2): self
    {
        $this->prefix2 = $prefix2;

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

    public function getDisplayOldName(): ?bool
    {
        return $this->displayOldName;
    }

    public function setDisplayOldName(bool $displayOldName): self
    {
        $this->displayOldName = $displayOldName;

        return $this;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function setCountry(Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @psalm-return ArrayCollection<int, Department>
     */
    public function getDepartments(): Collection
    {
        return $this->departments;
    }

    public function addDepartment(Department $department): self
    {
        if (!$this->departments->contains($department)) {
            $this->departments[] = $department;
            $department->setRegion($this);
        }

        return $this;
    }

    public function removeDepartment(Department $department): self
    {
        $this->departments->removeElement($department);

        return $this;
    }
}
