<?php

namespace App\Infrastructure\Doctrine\Types;

use App\Domain\Booking\Status;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\Exception\NotSupported;

/**
 * @extends AbstractEnumType<Status>
 */
final class BookingStatusType extends AbstractEnumType
{
    protected string $name = 'booking_status';

    protected function getCases(): array
    {
        return Status::cases();
    }

    protected function tryFrom(?string $value): mixed
    {
        $value = Status::tryFrom((string) $value);
        if (null === $value) {
            throw new NotSupported('Status does not support '.$value.' value');
        }

        return $value;
    }

    public function getMappedDatabaseTypes(AbstractPlatform $platform): array
    {
        return parent::getMappedDatabaseTypes($platform);
    }
}
