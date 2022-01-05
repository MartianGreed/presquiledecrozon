<?php

namespace App\Entity\Data;

use App\Repository\Data\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
class Department
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $slug = null;

    #[ORM\ManyToOne(targetEntity: Region::class, inversedBy: 'departments')]
    #[ORM\JoinColumn(nullable: false)]
    private Region $region;

    /** @var ArrayCollection<int, PostalCode> */
    #[ORM\OneToMany(mappedBy: 'department', targetEntity: PostalCode::class)]
    private Collection $postalCodes;

    public function __construct()
    {
        $this->postalCodes = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getRegion(): Region
    {
        return $this->region;
    }

    public function setRegion(Region $region): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @psalm-return ArrayCollection<int, PostalCode>
     */
    public function getPostalCodes(): Collection
    {
        return $this->postalCodes;
    }

    public function addPostalCode(PostalCode $postalCode): self
    {
        if (!$this->postalCodes->contains($postalCode)) {
            $this->postalCodes[] = $postalCode;
            $postalCode->setDepartment($this);
        }

        return $this;
    }

    public function removePostalCode(PostalCode $postalCode): self
    {
        $this->postalCodes->removeElement($postalCode);

        return $this;
    }
}
