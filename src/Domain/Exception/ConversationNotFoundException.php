<?php

namespace App\Domain\Exception;

final class ConversationNotFoundException extends \DomainException
{
    public function __construct(string $id)
    {
        parent::__construct('Could not find conversation with id : ' . $id);
    }
}