<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use App\Enum\ItemType;
use App\Enum\Unit;

class ItemDTO
{
    private const KILOGRAMS_VALUE = 1000;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    private string $name;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\GreaterThanOrEqual(0)]
    private int $quantity;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ItemType::VALUES)]
    private string $type;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: Unit::VALUES)]
    private string $unit;

    public function __construct(array $data)
    {
        $this->validate($data);

        $this->name = $data['name'];
        $this->quantity = $this->convertToGrams($data['quantity'], $data['unit']);
        $this->unit = $data['unit'];
        $this->type = $data['type'];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getQuantity(): int
    {
        if ($this->unit === Unit::KILOGRAMS->value) {
            return $this->quantity * $this::KILOGRAMS_VALUE;
        }
        return $this->quantity;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getUnit(): string
    {
        if ($this->unit === Unit::KILOGRAMS->value) {
            return Unit::GRAMS->value;
        }
        return $this->unit;
    }

    private function convertToGrams(int $quantity, string $unit): int
    {
        return $unit === Unit::KILOGRAMS ? $quantity * $this::KILOGRAMS_VALUE : $quantity;
    }

    private function validate(array $data): void
    {
        $validator = Validation::createValidator();

        $constraints = new Assert\Collection([
            'name' => [new Assert\NotBlank(), new Assert\Type('string')],
            'quantity' => [
                new Assert\NotBlank(),
                new Assert\Type('integer'),
                new Assert\GreaterThanOrEqual(0),
            ],
            'type' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Choice(array_column(ItemType::cases(), 'value')),
            ],
            'unit' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Choice(array_column(Unit::cases(), 'value')),
            ],
        ]);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            throw new \InvalidArgumentException(implode('; ', $errors));
        }
    }
}
