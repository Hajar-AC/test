<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TaskControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/api/task';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Task::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $task1 = new Task();
        $task1->setTitle('Task 1');
        $task1->setDescription('Description 1');
        $task1->setStatus('todo');
        $task1->setCreatedAt(new \DateTime());
        $task1->setUpdatedAt(new \DateTime());
        $this->manager->persist($task1);

        $task2 = new Task();
        $task2->setTitle('Task 2');
        $task2->setDescription('Description 2');
        $task2->setStatus('in_progress');
        $task2->setCreatedAt(new \DateTime());
        $task2->setUpdatedAt(new \DateTime());
        $this->manager->persist($task2);

        $this->manager->flush();

        $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        self::assertCount(2, $responseData['tasks']);
    }

    public function testNew(): void
    {
        $data = [
            'title' => 'New Task',
            'description' => 'New Description',
            'status' => 'todo',
        ];

        $this->client->request(
            'POST',
            $this->path . '/new',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        self::assertResponseStatusCodeSame(201);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        self::assertArrayHasKey('status', $responseData);
        self::assertEquals('Task created', $responseData['status']);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $task = new Task();
        $task->setTitle('Task to Show');
        $task->setDescription('Description to Show');
        $task->setStatus('done');
        $task->setCreatedAt(new \DateTime());
        $task->setUpdatedAt(new \DateTime());
        $this->manager->persist($task);
        $this->manager->flush();

        $this->client->request('GET', $this->path . '/' . $task->getId());

        self::assertResponseStatusCodeSame(200);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        self::assertEquals($task->getTitle(), $responseData['title']);
        self::assertEquals($task->getDescription(), $responseData['description']);
        self::assertEquals($task->getStatus(), $responseData['status']);
    }

    public function testEdit(): void
    {
        $task = new Task();
        $task->setTitle('Old Title');
        $task->setDescription('Old Description');
        $task->setStatus('todo');
        $task->setCreatedAt(new \DateTime());
        $task->setUpdatedAt(new \DateTime());
        $this->manager->persist($task);
        $this->manager->flush();

        $updatedData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'status' => 'in_progress',
        ];

        $this->client->request(
            'PUT',
            $this->path . '/' . $task->getId() . '/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updatedData)
        );

        self::assertResponseStatusCodeSame(200);
        $updatedTask = $this->repository->find($task->getId());

        self::assertEquals('Updated Title', $updatedTask->getTitle());
        self::assertEquals('Updated Description', $updatedTask->getDescription());
        self::assertEquals('in_progress', $updatedTask->getStatus());
    }

    public function testRemove(): void
    {
        $task = new Task();
        $task->setTitle('Task to Delete');
        $task->setDescription('Description to Delete');
        $task->setStatus('done');
        $task->setCreatedAt(new \DateTime());
        $task->setUpdatedAt(new \DateTime());
        $this->manager->persist($task);
        $this->manager->flush();

        $this->client->request('DELETE', $this->path . '/' . $task->getId());

        self::assertResponseStatusCodeSame(200);
        self::assertSame(0, $this->repository->count([]));
    }
}
