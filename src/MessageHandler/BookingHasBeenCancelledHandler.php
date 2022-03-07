<?php

namespace App\MessageHandler;

use App\Message\BookingHasBeenCancelled;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class BookingHasBeenCancelledHandler implements MessageHandlerInterface
{
    public function __invoke(BookingHasBeenCancelled $message)
    {
        // do something with your message
    }
}
