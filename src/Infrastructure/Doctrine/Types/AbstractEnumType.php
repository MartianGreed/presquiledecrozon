<?php

namespace App\Infrastructure\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * @template T
 */
abstract class AbstractEnumType extends Type
{
    protected string $name;

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        $values = array_map(static fn ($val) => "'" . $val->value . "'", $this->getCases());

        return "ENUM(" . implode(", ", $values) . ")";
    }

    /**
     * @param string $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        return $this->tryFrom($value);
    }

    /**
     * @param T $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return $value->value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    /**
     * @return non-empty-array<T>
     */
    abstract protected function getCases(): array;

    abstract protected function tryFrom(?string $value): mixed;
}
