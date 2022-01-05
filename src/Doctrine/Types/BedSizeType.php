<?php

namespace App\Doctrine\Types;

use App\Domain\Rental\BedSize;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\ORM\Exception\NotSupported;
use JsonException;

final class BedSizeType extends JsonType
{
    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof BedSize) {
            throw new NotSupported('Value has to be of type BedSize to use this doctrine type');
        }

        return (string) $value;
    }

    /**
     * {@inheritdoc}
     * @param ?string $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            /** @var array<string, int> $sizeObj */
            $sizeObj = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            return new BedSize((int) $sizeObj['height'], (int) $sizeObj['width']);
        } catch (JsonException $e) {
            throw ConversionException::conversionFailed($value, $this->getName(), $e);
        }
    }
}
