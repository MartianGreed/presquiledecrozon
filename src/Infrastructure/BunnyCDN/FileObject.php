<?php

namespace App\Infrastructure\BunnyCDN;

/**
 * @phpstan-type BunnyCDNResponseItem array{Guid: string, StorageZoneName: string, Path: string, ObjectName: string, Length: int, LastChanged: string, ServerId: int, ArrayNumber: int, IsDirectory: bool, UserId: string, ContentType: string, DateCreated: string, StorageZoneId: int, Checksum: null|string}
 */
final class FileObject
{
    private function __construct(
        public readonly string $guid,
        public readonly string $storageZoneName,
        public readonly string $path,
        public readonly string $objectName,
        public readonly int $length,
        public readonly string $lastChanged,
        public readonly int $serverId,
        public readonly int $arrayNumber,
        public readonly bool $isDirectory,
        public readonly string $userId,
        public readonly string $contentType,
        public readonly string $createdAt,
        public readonly int $storageZoneId,
        public readonly ?string $checksum,
    ) {
    }

    /**
     * @param BunnyCDNResponseItem $object
     */
    public static function fromArray(array $object): self
    {
        return new self(
            $object['Guid'],
            $object['StorageZoneName'],
            $object['Path'],
            $object['ObjectName'],
            $object['Length'],
            $object['LastChanged'],
            $object['ServerId'],
            $object['ArrayNumber'],
            $object['IsDirectory'],
            $object['UserId'],
            $object['ContentType'],
            $object['DateCreated'],
            $object['StorageZoneId'],
            $object['Checksum'],
        );
    }

    public function getFilePath(): string
    {
        return $this->path . $this->objectName;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getFilename(): string
    {
        return $this->objectName;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getLastChanged(): \DateTimeInterface
    {
        return new \DateTime($this->lastChanged);
    }

    public function isDirectory(): bool
    {
        return $this->isDirectory;
    }

    public function getMimeType(): string
    {
        return $this->contentType;
    }
}
