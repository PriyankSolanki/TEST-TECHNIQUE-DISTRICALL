<?php

namespace App\Tests\Controller;
use App\Repository\TaskRepository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    public function testCreateTask(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/task', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'title' => 'Test Task',
            'description' => 'This is a test task',
            'status' => 'todo',
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($client->getResponse()->getContent());
    }
        
    public function testGetTaskByTitleOrDescription(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/task', ['title' => 'Test']);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($client->getResponse()->getContent());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

    public function testGetTaskPerPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/task/perPage', ['page' => 1]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($client->getResponse()->getContent());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('total_pages', $data);
    }
    
    public function testUpdateTask(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $taskRepository = $container->get(TaskRepository::class);
        $lastTask = $taskRepository->findOneBy([], ['id' => 'DESC']);
        $this->assertNotNull($lastTask, 'Aucune tâche trouvée dans la base de données.');

        $lastTaskId = $lastTask->getId();
        $client->request(
            'PUT',
            '/api/task/'.$lastTaskId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'title' => 'Updated Task',
                'description' => 'Updated description',
                'status' => 'in_progress'
            ])
        );

        $this->assertResponseStatusCodeSame(200);
    }
    
    public function testDeleteTask(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $taskRepository = $container->get(TaskRepository::class);
        $lastTask = $taskRepository->findOneBy([], ['id' => 'DESC']);
        $this->assertNotNull($lastTask, 'Aucune tâche trouvée dans la base de données.');

        $lastTaskId = $lastTask->getId();
        $client->request('DELETE', '/api/task/'.$lastTaskId);

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Tâche supprimée avec succès.', $data['message']);
    }

}
