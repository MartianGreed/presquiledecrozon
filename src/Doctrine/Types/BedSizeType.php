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
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof BedSize) {
            throw new NotSupported('Value has to be of type BedSize to use this doctrine type');
        }

        try {
            return (string) $value;
        } catch (JsonException $e) {
            throw ConversionException::conversionFailedSerialization($value, 'json', $e->getMessage(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        try {
            $sizeObj = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            return new BedSize((int) $sizeObj['height'], (int) $sizeObj['width']);
        } catch (JsonException $e) {
            throw ConversionException::conversionFailed($value, $this->getName(), $e);
        }
    }
}