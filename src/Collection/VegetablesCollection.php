<?php

namespace App\Collection;

use App\Enum\ItemType;

class VegetablesCollection extends BaseCollection
{
    public function supportsType(string $type): bool
    {
        return $type === ItemType::VEGETABLE->value;
    }
}
