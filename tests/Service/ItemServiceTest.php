<?php

namespace App\Tests\Service;

use App\DTO\ItemDTO;
use App\Entity\Item;
use App\Enum\ItemType;
use App\Enum\Unit;
use App\Exception\ItemNotFoundException;
use App\Repository\ItemRepository;
use App\Service\ItemService;
use PHPUnit\Framework\TestCase;

class ItemServiceTest extends TestCase
{
    private ItemRepository $repository;
    private ItemService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ItemRepository::class);
        $this->service = new ItemService($this->repository);
    }

    public function testAddItem(): void
    {
        $dto = new ItemDTO([
            'name' => 'Banana',
            'quantity' => 2,
            'type' => 'fruit', 
            'unit' => 'kg'     
        ]);

        $result = $this->service->createItem($dto);

        $this->assertEquals('Banana', $result->getName());
        $this->assertEquals(2000, $result->getQuantity());
        $this->assertEquals(ItemType::FRUIT, $result->getType());
    }

    public function testListItemsWithFilters(): void
    {
        $item = (new Item())
            ->setName('Apple')
            ->setQuantity(1000)
            ->setType(ItemType::from('fruit')) 
            ->setUnit(Unit::from('g'));       

        $this->repository
            ->expects($this->once())
            ->method('findByType')
            ->with(ItemType::from('fruit')) 
            ->willReturn([$item]);

        $items = $this->service->listItems(ItemType::from('fruit'), Unit::from('kg'));

        $this->assertCount(1, $items);
        $this->assertEquals(1, $items[0]['quantity']);
        $this->assertEquals(Unit::KILOGRAMS, $items[0]['unit']);
    }

    public function testUpdateItem(): void
    {
        $existingItem = (new Item())
            ->setName('Apple')
            ->setQuantity(500)
            ->setType(ItemType::from('fruit'));

        $dto = new ItemDTO([
            'name' => 'Updated Apple',
            'quantity' => 700,
            'type' => 'fruit', 
            'unit' => 'g'  
        ]);

        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($existingItem);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->equalTo($existingItem));

        $updatedItem = $this->service->updateItem(1, $dto);

        $this->assertEquals('Updated Apple', $updatedItem->getName());
        $this->assertEquals(700, $updatedItem->getQuantity());
    }

    public function testGetItemById(): void
    {
        $item = (new Item())
            ->setName('Apple')
            ->setQuantity(500)
            ->setType(ItemType::from('fruit'));

        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($item);

        $result = $this->service->getItemById(1);

        $this->assertEquals('Apple', $result->getName());
        $this->assertEquals(500, $result->getQuantity());
    }

    public function testDeleteItem(): void
    {
        $existingItem = (new Item())
            ->setName('Apple')
            ->setQuantity(500)
            ->setType(ItemType::from('fruit'));

        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($existingItem);

        $this->repository
            ->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($existingItem));

        $this->service->deleteItem(1);
    }

    public function testItemNotFound(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $this->expectException(ItemNotFoundException::class);

        $this->service->getItemById(999);
    }

    public function testInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("[type]: The value you selected is not a valid choice.");
    
        $dto = new ItemDTO([
            'name' => 'Invalid Item',
            'quantity' => 10,
            'type' => 'invalidType', 
            'unit' => 'g'
        ]);
    }

}
