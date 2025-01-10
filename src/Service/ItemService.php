<?php

namespace App\Service;

use App\DTO\ItemDTO;
use App\Entity\Item;
use App\Enum\ItemType;
use App\Enum\Unit;
use App\Exception\InvalidTypeException;
use App\Repository\ItemRepository;
use App\Exception\ItemNotFoundException;

class ItemService
{
    private ItemRepository $repository;

    public function __construct(ItemRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createItem(ItemDTO $dto): Item
    {
        $item = (new Item())
            ->setName($dto->getName())
            ->setType(ItemType::from($dto->getType()))
            ->setUnit(Unit::From($dto->getUnit()))
            ->setQuantity($dto->getQuantity());

        $this->repository->save($item);

        return $item;
    }

    public function listItems(?ItemType $type, Unit $unit = Unit::GRAMS): array
    {
        $items = $type ? $this->repository->findByType($type) : $this->repository->findAll();

        /*if (!$type) {
            throw new InvalidTypeException("Type incorrect");
        }*/

        return array_map(function (Item $item) use ($unit) {
            $quantity = $unit === Unit::KILOGRAMS && $item->getUnit() === Unit::GRAMS
                ? $item->getQuantity() / 1000
                : $item->getQuantity();

            return [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'quantity' => $quantity,
                'unit' => $unit,
                'type' => $item->getType()->value,
            ];
        }, $items);
    }

    public function getItemById(int $id): Item
    {
        $item = $this->repository->find($id);

        if (!$item) {
            throw new ItemNotFoundException("Item with ID $id not found.");
        }

        return $item;
    }

    public function updateItem(int $id, ItemDTO $dto): Item
    {
        $item = $this->repository->find($id);

        if (!$item) {
            throw new ItemNotFoundException("Item with ID $id not found.");
        }

        $item->setName($dto->getName())
            ->setQuantity($dto->getQuantity())
            ->setType(ItemType::from($dto->getType()))
            ->setUnit(Unit::From($dto->getUnit()));

        $this->repository->save($item);

        return $item;
    }

    public function deleteItem(int $id): void
    {
        $item = $this->repository->find($id);

        if (!$item) {
            throw new ItemNotFoundException("Item with ID $id not found.");
        }

        $this->repository->remove($item);
    }

    public function searchItems(string $searchTerm): array
    {
        $items = $this->repository->searchByName($searchTerm);

        return array_map(function (Item $item) {
            return [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'quantity' => $item->getQuantity(),
                'unit' => $item->getUnit()->value,
                'type' => $item->getType()->value,
            ];
        }, $items);
    }
}
