<?php

namespace App\Collection;

use App\Entity\Item;
use App\Enum\Unit;

interface CollectionInterface
{
    public function add(Item $item): void;

    public function remove(int $id): void;

    public function list(Unit $unit = Unit::GRAMS): array;

    public function getById(int $id): Item;

    public function update(int $id, string $name, int $quantity): void;

    public function supportsType(string $type): bool;
}
