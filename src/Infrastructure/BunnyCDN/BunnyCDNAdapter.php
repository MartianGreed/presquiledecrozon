<?php

namespace App\Infrastructure\BunnyCDN;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToMoveFile;

final class BunnyCDNAdapter implements FilesystemAdapter
{
    private readonly PathPrefixer $prefixer;

    public function __construct(
        private readonly Storage $storage,
        string $prefix = '',
    ) {
        $this->prefixer = new PathPrefixer($prefix);
    }

    public function fileExists(string $path): bool
    {
        $path = $this->prefixer->prefixPath($path);
        return $this->storage->fileExists($path);
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $path = $this->prefixer->prefixPath($path);
        $this->storage->uploadFile($path, $contents);
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $path = $this->prefixer->prefixPath($path);

        $this->storage->uploadFile($path, $contents);
    }

    public function read(string $path): string
    {
        $path = $this->prefixer->prefixPath($path);
        return $this->storage->downloadFile($path)->getContentAsString();
    }

    public function readStream(string $path)
    {
        $path = $this->prefixer->prefixPath($path);
        return $this->storage->downloadFile($path)->getContentAsResource();
    }

    public function delete(string $path): void
    {
        $path = $this->prefixer->prefixPath($path);
        $this->storage->deleteObject($path);
    }

    public function deleteDirectory(string $path): void
    {
        $path = $this->prefixer->prefixPath($path);
        $this->storage->deleteObject($path);
    }

    public function createDirectory(string $path, Config $config): void
    {
        // BunnyCDN automatically creates missing directory on file upload.
        // we can just do nothing here.
    }

    public function setVisibility(string $path, string $visibility): void
    {
        // No visibility flag for BunnyCDN
        // everything is public.
    }

    public function visibility(string $path): FileAttributes
    {
        $path = $this->prefixer->prefixPath($path);
        return $this->fetchFileMetadata($path);
    }

    public function mimeType(string $path): FileAttributes
    {
        $path = $this->prefixer->prefixPath($path);
        return $this->fetchFileMetadata($path);
    }

    public function lastModified(string $path): FileAttributes
    {
        $path = $this->prefixer->prefixPath($path);
        return $this->fetchFileMetadata($path);
    }

    public function fileSize(string $path): FileAttributes
    {
        $path = $this->prefixer->prefixPath($path);
        return $this->fetchFileMetadata($path);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $path = $this->prefixer->prefixPath($path);
        $response = $this->storage->getStorageObjects($path);

        /** @var FileObject $object */
        foreach ($response->getContent() as $object) {
            yield FileAttributesFactory::from($object);
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $source = $this->prefixer->prefixPath($source);
        $destination = $this->prefixer->prefixPath($destination);
        try {
            $this->copy($source, $destination, $config);
            $this->delete($source);
        } catch (\Exception) {
            throw UnableToMoveFile::fromLocationTo($source, $destination);
        }
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $source = $this->prefixer->prefixPath($source);
        $destination = $this->prefixer->prefixPath($destination);
        $response = $this->storage->uploadFile($destination, $this->readStream($source));

        if (201 !== $response->getStatusCode()) {
            throw UnableToCopyFile::fromLocationTo($source, $destination);
        }
    }

    private function fetchFileMetadata(string $path): FileAttributes
    {
        $path = $this->prefixer->prefixPath($path);
        $object = $this->storage->getObject($path);
        return FileAttributesFactory::from($object);
    }
}
