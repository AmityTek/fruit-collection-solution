<?php

namespace App\Tests\Controller;

use App\Repository\ItemRepository;
use App\Service\ItemService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CollectionControllerTest extends WebTestCase
{

    public function testAddItemViaJsonPayload(): void
    {
        $client = static::createClient();

        $payload = [
            'name' => 'BananaSurfer',
            'quantity' => 2,
            'type' => 'fruit',
            'unit' => 'kg'
        ];

        $client->request(
            'POST',
            '/api/items',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Item created successfully.', $data['message']);
    }


    public function testUploadJsonFile(): void
    {
        $client = static::createClient();

        $filePath = __DIR__ . '/../Fixtures/request.json';
        $file = new \Symfony\Component\HttpFoundation\File\UploadedFile(
            $filePath,
            'request.json',
            'application/json',
            null
        );

        $client->request(
            'POST',
            '/api/upload',
            [],
            ['file' => $file],
            ['CONTENT_TYPE' => 'multipart/form-data']
        );

        $this->assertResponseStatusCodeSame(201);

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('File processed successfully.', $data['message']);
    }

    public function testListItemById(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/items/1');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testListItemsWithType(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/items?type=fruit');

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true);
        foreach ($data as $item) {
            $this->assertEquals('fruit', $item['type']);
        }
    }

    public function testListItemsWithUnit(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/items?unit=kg');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testListItemsWithTypeAndUnit(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/items?type=fruit&unit=kg');

        $this->assertResponseStatusCodeSame(200);
        $response = $client->getResponse();
        $this->assertJson($response->getContent());

        $items = json_decode($response->getContent(), true);
        $this->assertGreaterThanOrEqual(0, count($items));
    }

    public function testSearchItems(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/items', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Apple',
            'quantity' => 5,
            'type' => 'fruit',
            'unit' => 'kg',
        ]));

        $client->request('GET', '/api/items/search?name=Apple');

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertGreaterThan(0, count($data));
    }

    public function testDeleteItemViaApi(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/items');
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true);
        $lastItem = end($data);

        $this->assertArrayHasKey('id', $lastItem);

        $client->request('DELETE', '/api/items/' . $lastItem['id']);

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Item deleted successfully.', $data['message']);
    }

    public function testCreateItemWithInvalidData(): void
    {
        $client = static::createClient();

        $data = [
            'name' => 'Invalid Item',
            'quantity' => -1,
            'type' => 'invalidType',
            'unit' => 'kg',
        ];

        $client->request('POST', '/api/items', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testDeleteItemNotFound(): void
    {
        $client = static::createClient();

        $client->request('DELETE', '/api/items/99999');

        $this->assertResponseStatusCodeSame(404);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Item with ID 99999 not found.', $data['error']);
    }
}
