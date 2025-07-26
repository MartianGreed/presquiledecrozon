<?php

namespace App\Infrastructure\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * @template T of \BackedEnum
 */
abstract class AbstractEnumType extends Type
{
    protected string $name;

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        $values = array_map(static function ($val) {
            return "'" . $val->value . "'";
        }, $this->getCases());

        return 'ENUM(' . implode(', ', $values) . ')';
    }

    /**
     * @param string $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        return $this->tryFrom($value);
    }

    /**
     * @param T|null $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return null;
        }

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
