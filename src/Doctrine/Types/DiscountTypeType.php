<?php

namespace App\Doctrine\Types;

use App\Domain\Rental\Status;
use App\Domain\Subscription\DiscountType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\Exception\NotSupported;

/**
 * @extends AbstractEnumType<DiscountType>
 */
final class DiscountTypeType extends AbstractEnumType
{
    protected string $name = 'discount_type';

    protected function getCases(): array
    {
        return DiscountType::cases();
    }

    protected function tryFrom(string $value): mixed
    {
        $value = DiscountType::tryFrom($value);
        if (null === $value) {
            throw new NotSupported('DiscountType does not support ' . $value . ' value');
        }

        return $value;
    }

    public function getMappedDatabaseTypes(AbstractPlatform $platform): array
    {
        return parent::getMappedDatabaseTypes($platform);
    }
}
