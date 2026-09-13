<?php

namespace App\Infrastructure\BunnyCDN;

use League\Flysystem\FileAttributes;

final class FileAttributesFactory
{
    public static function from(FileObject $object): FileAttributes
    {
        return new FileAttributes(
            $object->getFilePath(),
            $object->getLength(),
            'public',
            $object->getLastChanged()->getTimestamp(),
            $object->getMimeType(),
        );
    }
}
