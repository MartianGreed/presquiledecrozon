<?php

namespace App\Domain\Rental\DTO;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class Pictures
{
    public UploadedFile $mainPicture;

    public UploadedFile $picture_0;

    public UploadedFile $picture_1;

    public UploadedFile $picture_2;

    public UploadedFile $picture_3;

    public UploadedFile $picture_4;
}
