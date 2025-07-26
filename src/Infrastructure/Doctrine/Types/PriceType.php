<?php

namespace App\Infrastructure\Doctrine\Types;

use App\Domain\Price;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;

final class PriceType extends IntegerType
{
    public function getName(): string
    {
        return 'price';
    }

    /**
     * @param mixed $value
     * @return Price|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Price
    {
        if ($value === null) {
            return null;
        }
        
        $intValue = is_numeric($value) ? (int) $value : 0;
        return new Price($intValue / 100);
    }

    /** @param ?Price $value */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return parent::convertToDatabaseValue($value?->getValue(), $platform);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
