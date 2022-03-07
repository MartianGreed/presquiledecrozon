<?php

namespace App\MessageHandler;

use App\Message\BookingHasBeenConfirmed;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class BookingHasBeenConfirmedHandler implements MessageHandlerInterface
{
    public function __invoke(BookingHasBeenConfirmed $message)
    {
        // do something with your message
    }
}
