<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/task')]
final class TaskController extends AbstractController
{
    #[Route('', name: 'app_task_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository, Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1); 
        $limit = 10; 
        $search = $request->query->get('search', ''); 

        $queryBuilder = $taskRepository->createQueryBuilder('t')
            ->orderBy('t.created_at', 'DESC');

        
        if (!empty($search)) {
            $queryBuilder->andWhere('t.title LIKE :search OR t.description LIKE :search')
                         ->setParameter('search', '%' . $search . '%');
        }

        
        $query = $queryBuilder
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        $paginator = new Paginator($query, true);

        
        $tasks = [];
        foreach ($paginator as $task) {
            $tasks[] = [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
                'created_at' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $task->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse([
            'tasks' => $tasks,
            'current_page' => $page,
            'total_items' => count($paginator),
            'total_pages' => ceil(count($paginator) / $limit),
        ]);
    }

    #[Route('/new', name: 'app_task_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['title'], $data['description'], $data['status'])) {
            return new JsonResponse(['error' => 'Invalid data provided'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $task = new Task();
        $task->setTitle($data['title']);
        $task->setDescription($data['description']);
        $task->setStatus($data['status']);
        $task->setCreatedAt(new \DateTime());
        $task->setUpdatedAt(new \DateTime());

        $entityManager->persist($task);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Task created', 'task' => $task->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'app_task_show', methods: ['GET'])]
    public function show(Task $task): JsonResponse
    {
        return new JsonResponse([
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus(),
            'created_at' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $task->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['PUT'])]
    public function edit(Request $request, Task $task, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Invalid data provided'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $task->setTitle($data['title'] ?? $task->getTitle());
        $task->setDescription($data['description'] ?? $task->getDescription());
        $task->setStatus($data['status'] ?? $task->getStatus());
        $task->setUpdatedAt(new \DateTime());

        $entityManager->flush();

        return new JsonResponse(['status' => 'Task updated']);
    }

    #[Route('/{id}', name: 'app_task_delete', methods: ['DELETE'])]
    public function delete(Task $task, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($task);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Task deleted']);
    }
}
