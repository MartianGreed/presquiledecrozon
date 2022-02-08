<?php

namespace App\MessageHandler;

use App\Message\RentalHasBeenPublished;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class RentalHasBeenPublishedHandler implements MessageHandlerInterface
{
    public function __invoke(RentalHasBeenPublished $message): void
    {
        // do something with your message
    }
}
