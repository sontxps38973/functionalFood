<?php

namespace App\Support;

class Currency
{
    public static function toVndInt($value): int
    {
        if ($value === null) {
            return 0;
        }
        return (int) round((float) $value);
    }
}