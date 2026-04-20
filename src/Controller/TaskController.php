<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'app_tasks_index', methods: ['GET'])]
    public function index(TaskRepository $repo): JsonResponse
    {
        $tasks = $repo->findAll();

        $data = array_map(fn($task) => [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus(),
            'createdAt' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
            'priority' => $task->getPriority(),
        ], $tasks);

        return $this->json($data);
    }


    #[Route('/tasks', name: 'app_tasks_create', methods: ['POST'])]
public function create(Request $request, EntityManagerInterface $em): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    if (!$data || !isset($data['title']) || !isset($data['priority'])) {
        return $this->json(['error' => 'Invalid JSON or missing fields'], 400);
    }

    // Vérifier que la priorité est valide
    $validPriorities = ['low', 'medium', 'high'];
    if (!in_array($data['priority'], $validPriorities, true)) {
        return $this->json(['error' => 'Invalid priority value'], 400);
    }

    $task = new Task();
    $task->setTitle($data['title']);
    $task->setDescription($data['description'] ?? null);
    $task->setStatus($data['status'] ?? 'todo');
    $task->setCreatedAt(new \DateTimeImmutable());
    $task->setPriority($data['priority']); // ✔️ maintenant garanti non-null et valide

    $em->persist($task);
    $em->flush();

    return $this->json([
        'message' => 'Task created',
        'id' => $task->getId()
    ], 201);
}


    #[Route('/tasks/{id}', name: 'app_tasks_show', methods: ['GET'])]
    public function show(Task $task): JsonResponse
    {
        return $this->json([
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus(),
            'createdAt' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
            'priority' => $task->getPriority(),
        ]);
    }


    #[Route('/tasks/{id}', name: 'tasks_update', methods: ['PATCH'])]
    public function update(int $id, Request $request, TaskRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $task = $repo->find($id);
        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        // PATCH = mise à jour partielle
        if (isset($data['title'])) {
            $task->setTitle($data['title']);
        }

        if (isset($data['description'])) {
            $task->setDescription($data['description']);
        }

        if (isset($data['status'])) {
            $task->setStatus($data['status']);
        }

        if (isset($data['priority'])) {
            $task->setPriority($data['priority']);
        }

        $em->flush();

        return $this->json(['message' => 'Task updated']);
    }


    #[Route('/tasks/{id}', name: 'tasks_delete', methods: ['DELETE'])]
    public function delete(int $id, TaskRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $task = $repo->find($id);
        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }

        $em->remove($task);
        $em->flush();

        return $this->json(['message' => 'Task deleted']);
    }

}
