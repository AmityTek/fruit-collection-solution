<?php

namespace App\Collection;

use App\Enum\ItemType;

class FruitsCollection extends BaseCollection
{
    public function supportsType(string $type): bool
    {
        return $type === ItemType::FRUIT->value;
    }
}
