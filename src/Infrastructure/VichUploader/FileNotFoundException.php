<?php

namespace App\Infrastructure\VichUploader;

final class FileNotFoundException extends \RuntimeException
{
    public function __construct(string $fileName)
    {
        parent::__construct(
            sprintf('File not found on CDN for path %s', $fileName),
            0,
            null
        );
    }
}
