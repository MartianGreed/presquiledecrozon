<?php

namespace App\Infrastructure\Doctrine\Types;

use App\Domain\Booking\BookingPrices;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\ORM\Exception\NotSupported;
use JsonException;

final class BookingPricesType extends JsonType
{
    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof BookingPrices) {
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
            /** @var array<array{count: int, price: float}> $priceObj */
            $priceObj = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            return BookingPrices::fromArray($priceObj);
        } catch (JsonException $e) {
            throw ConversionException::conversionFailed($value, $this->getName(), $e);
        }
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function getName()
    {
        return 'booking_prices';
    }
}
