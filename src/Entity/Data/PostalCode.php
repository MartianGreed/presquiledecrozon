<?php

namespace App\Entity\Data;

use App\Repository\Data\PostalCodeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: PostalCodeRepository::class)]
class PostalCode implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $code = null;

    #[ORM\ManyToOne(targetEntity: Department::class, inversedBy: 'postalCodes')]
    #[ORM\JoinColumn(nullable: false)]
    private Department $department;

    /** @var ArrayCollection<int, Town>  */
    #[ORM\OneToMany(mappedBy: 'postalCode', targetEntity: Town::class)]
    private Collection $towns;

    public function __construct()
    {
        $this->towns = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->code ?? '';
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getDepartment(): Department
    {
        return $this->department;
    }

    public function setDepartment(Department $department): self
    {
        $this->department = $department;

        return $this;
    }

    /**
     * @psalm-return ArrayCollection<int, Town>
     */
    public function getTowns(): Collection
    {
        return $this->towns;
    }

    public function addTown(Town $town): self
    {
        if (!$this->towns->contains($town)) {
            $this->towns[] = $town;
            $town->setPostalCode($this);
        }

        return $this;
    }

    public function removeTown(Town $town): self
    {
        $this->towns->removeElement($town);

        return $this;
    }
}
