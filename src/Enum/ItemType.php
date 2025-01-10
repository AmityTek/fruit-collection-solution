<?php

namespace App\Enum;

enum ItemType: string
{
    case FRUIT = 'fruit';
    case VEGETABLE = 'vegetable';

    public const VALUES = [
        self::FRUIT,
        self::VEGETABLE,
    ];
}
