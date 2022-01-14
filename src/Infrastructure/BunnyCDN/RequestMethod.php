<?php

namespace App\Infrastructure\BunnyCDN;

enum RequestMethod: string
{
    case GET = 'GET';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
}
