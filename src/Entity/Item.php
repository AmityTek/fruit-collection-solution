<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Enum\ItemType;
use App\Enum\Unit;
use App\Repository\ItemRepository;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(type: 'integer')]
    private int $quantity; // Stored in grams

    #[ORM\Column(type: 'string', length: 50)]
    private string $type;

    #[ORM\Column(type: 'string', length: 15)]
    private string $unit; 

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getType(): ItemType
    {
        return ItemType::from($this->type);
    }

    public function setType(ItemType $type): self
    {
        $this->type = $type->value;
        return $this;
    }

    public function getUnit(): Unit
    {
        return Unit::from($this->unit);
    }

    public function setUnit(Unit $unit): self
    {
        $this->unit = $unit->value;
        return $this;
    }
}
