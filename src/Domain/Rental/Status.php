<?php

namespace App\Domain\Rental;

enum Status: string
{
    case DRAFT = 'draft';
    case IN_PROGRESS = 'in-progress';
    case VALID = 'valid';
    case PUBLISHED = 'published';
    case DISABLED = 'disabled';

    public function translate(): string
    {
        return match($this) {
            self::DRAFT => 'Brouillon',
            self::IN_PROGRESS => 'En cours',
            self::VALID => 'Valide',
            self::PUBLISHED => 'Publiée',
            self::DISABLED => 'Désactivée',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::DRAFT => 'info',
            self::IN_PROGRESS => 'info',
            self::VALID => 'primary',
            self::PUBLISHED => 'success',
            self::DISABLED => 'danger',
        };
    }
}
