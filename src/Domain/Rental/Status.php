<?php

namespace App\Domain\Rental;

enum Status: string
{
    case DRAFT = 'draft';
    case IN_PROGRESS = 'in-progress';
    case VALID = 'valid';
    case PUBLISHED = 'published';
    case DISABLED = 'disabled';
}
