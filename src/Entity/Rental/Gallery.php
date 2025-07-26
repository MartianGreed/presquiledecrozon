<?php

namespace App\Entity\Rental;

use App\Entity\Identity;
use App\Entity\IdentityTrait;
use App\Entity\Media;
use App\Repository\Rental\GalleryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GalleryRepository::class)]
class Gallery implements Identity
{
    use IdentityTrait;

    #[ORM\ManyToOne(targetEntity: Media::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Media $cover = null;

    /**
     * @var ArrayCollection<int, Media>
     */
    #[ORM\ManyToMany(targetEntity: Media::class, cascade: ['persist', 'remove'])]
    private Collection $pictures;

    #[ORM\OneToOne(mappedBy: 'gallery', targetEntity: Rental::class, cascade: ['persist', 'remove'])]
    private ?Rental $rental = null;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
    }

    public function getCover(): ?Media
    {
        return $this->cover;
    }

    public function setCover(Media $cover): self
    {
        $this->cover = $cover;

        return $this;
    }

    /**
     * @psalm-return ArrayCollection<int, Media>
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Media $picture): self
    {
        if (! $this->pictures->contains($picture)) {
            $this->pictures[] = $picture;
        }

        return $this;
    }

    public function addPictureAtIndex(Media $picture, int $index): self
    {
        $this->pictures->offsetSet($index, $picture);

        return $this;
    }

    public function removePicture(Media $picture): self
    {
        $this->pictures->removeElement($picture);

        return $this;
    }

    public function getRental(): ?Rental
    {
        return $this->rental;
    }

    public function setRental(Rental $rental): void
    {
        $this->rental = $rental;
    }

    public function getPicturesCount(): int
    {
        // Cover has to be set so it makes one picture for sure
        return 1 + $this->pictures->count();
    }
}
