<?php

namespace App\Util;

final class Type
{
    /**
     * @template T
     *
     * @param ?T $var
     *
     * @return T
     */
    public static function assertNotNull($var)
    {
        if (null === $var) {
            throw new \RuntimeException('Variable cannot be null');
        }

        return $var;
    }
}
