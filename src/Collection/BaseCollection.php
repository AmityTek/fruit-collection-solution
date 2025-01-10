<?php

namespace App\Collection;

use App\Entity\Item;
use App\Enum\Unit;
use App\Exception\ItemNotFoundException;

abstract class BaseCollection implements CollectionInterface
{
    protected array $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    abstract public function supportsType(string $type): bool;

    public function add(Item $item): void
    {
        $this->items[] = $item;
    }

    public function remove(int $id): void
    {
        foreach ($this->items as $index => $item) {
            if ($item->getId() === $id) {
                unset($this->items[$index]);
                return;
            }
        }

        throw new ItemNotFoundException();
    }

    public function list(Unit $unit = Unit::GRAMS): array
    {
        return array_map(function (Item $item) use ($unit) {
            $quantity = $unit === Unit::KILOGRAMS ? $item->getQuantity() / 1000 : $item->getQuantity();
            return [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'quantity' => $quantity,
                'type' => $item->getType(),
                'unit' => $unit->value,
            ];
        }, $this->items);
    }

    public function update(int $id, string $name, int $quantity): void
    {
        $item = $this->getById($id);
        $item->setName($name);
        $item->setQuantity($quantity);
    }

    public function getById(int $id): Item
    {
        foreach ($this->items as $item) {
            if ($item->getId() === $id) {
                return $item;
            }
        }

        throw new ItemNotFoundException();
    }
}
