<?php

namespace App\Enum;

enum Unit: string
{
    case GRAMS = 'g';
    case KILOGRAMS = 'kg';

    public const VALUES = [
        self::GRAMS,
        self::KILOGRAMS,
    ];
}
