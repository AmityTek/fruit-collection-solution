<?php

namespace App\Controller;

use App\Collection\FruitCollection;
use App\DTO\ItemDTO;
use App\Enum\ItemType;
use App\Enum\Unit;
use App\Repository\ItemRepository;
use App\Service\ItemService;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CollectionController
{
    private ItemService $service;

    public function __construct(ItemService $service)
    {
        $this->service = $service;
    }

    /**
     * Create a new item (fruit or vegetable).
     */
    #[Route('/api/items', methods: ['POST'])]
    public function createItem(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $dto = new ItemDTO($data);

            $item = $this->service->createItem($dto);

            return new JsonResponse([
                'message' => 'Item created successfully.',
                'item' => [
                    'id' => $item->getId(),
                    'name' => $item->getName(),
                    'quantity' => $item->getQuantity(),
                    'type' => $item->getType()->value,
                ],
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get a list of all items, optionally filtered by type and unit.
     */
    #[Route('/api/items', methods: ['GET'])]
    public function listItems(Request $request): JsonResponse
    {
        try {
            $type = $request->query->get('type');
            $unit = $request->query->get('unit', Unit::GRAMS->value);

            $typeEnum = $type ? ItemType::tryFrom($type) : null;
            $unitEnum = Unit::tryFrom($unit);
            if (!$unitEnum) {
                $unitEnum = Unit::GRAMS;
            }

            $items = $this->service->listItems($typeEnum, $unitEnum);

            return new JsonResponse($items, 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Search for items by name.
     */
    #[Route('/api/items/search', methods: ['GET'])]
    public function searchItems(Request $request): JsonResponse
    {
        $name = $request->query->get('name');

        if (empty($name)) {
            return new JsonResponse(['error' => 'Name query parameter is required.'], 400);
        }

        $items = $this->service->searchItems($name);

        return new JsonResponse($items, 200);
    }

    /**
     * Get a specific item by ID.
     */
    #[Route('/api/items/{id}', methods: ['GET'])]
    public function getItem(int $id): JsonResponse
    {
        try {
            $item = $this->service->getItemById($id);

            return new JsonResponse([
                'id' => $item->getId(),
                'name' => $item->getName(),
                'quantity' => $item->getQuantity(),
                'type' => $item->getType()->value,
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Update an existing item.
     */
    #[Route('/api/items/{id}', methods: ['PUT'])]
    public function updateItem(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $dto = new ItemDTO($data);

            $updatedItem = $this->service->updateItem($id, $dto);

            return new JsonResponse([
                'message' => 'Item updated successfully.',
                'item' => [
                    'id' => $updatedItem->getId(),
                    'name' => $updatedItem->getName(),
                    'quantity' => $updatedItem->getQuantity(),
                    'type' => $updatedItem->getType()->value,
                ],
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete an existing item.
     */
    #[Route('/api/items/{id}', methods: ['DELETE'])]
    public function deleteItem(int $id): JsonResponse
    {
        try {
            $this->service->deleteItem($id);

            return new JsonResponse(['message' => 'Item deleted successfully.'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Upload a JSON file with items to create.
     */
    #[Route('/api/upload', methods: ['POST'])]
    public function uploadJsonFile(Request $request): JsonResponse
    {
        $file = $request->files->get('file');

        if (!$file || $file->getClientOriginalExtension() !== 'json') {
            return new JsonResponse(['error' => 'Invalid file. Please upload a JSON file.'], 400);
        }

        try {
            $fileContents = file_get_contents($file->getRealPath());
            $data = json_decode($fileContents, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse(['error' => 'Invalid JSON format in the file.'], 400);
            }

            $items = [];
            foreach ($data as $itemData) {
                if (isset($itemData['id'])) {
                    unset($itemData['id']);
                }
                $dto = new ItemDTO($itemData);
                $item = $this->service->createItem($dto);
                $items[] = [
                    'name' => $item->getName(),
                    'quantity' => $item->getQuantity(),
                    'unit' => Unit::GRAMS,
                    'type' => $item->getType()->value,
                ];
            }

            return new JsonResponse([
                'message' => 'File processed successfully.',
                'items' => $items,
            ], 201);
        } catch (FileException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}
