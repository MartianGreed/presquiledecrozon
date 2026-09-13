<?php

namespace App\Domain\Rental\DTO;

use App\Entity\Media;
use App\Infrastructure\Symfony\Validator\UploadPictureConstraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Valid;

#[UploadPictureConstraint]
final class UploadedPicture
{
    #[NotBlank]
    #[NotNull]
    #[Regex('/cover|picture/')]
    public string $field = '';

    #[Valid]
    public Media $media;

    public ?int $index = null;
}
