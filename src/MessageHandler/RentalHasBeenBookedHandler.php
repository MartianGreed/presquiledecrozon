<?php

namespace App\MessageHandler;

use App\Message\RentalHasBeenBooked;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class RentalHasBeenBookedHandler implements MessageHandlerInterface
{
    public function __invoke(RentalHasBeenBooked $message)
    {
        // do something with your message
    }
}
