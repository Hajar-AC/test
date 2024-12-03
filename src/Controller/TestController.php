<?php

namespace App\Controller;

use App\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{

    /*
    #[Route('/test-validation', name: 'test_validation')]
    public function testValidation(): JsonResponse
    {
        try {
            $task = new Task();
            $task->setStatus('invalid_status'); // exception

            return new JsonResponse(['message' => 'Validation passed, but this is unexpected.']);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
        */

        #[Route('/test-validation', name: 'test_validation')]
public function testValidation(): JsonResponse
{
    try {
        $task = new Task();
        $task->setStatus(Task::STATUS_DONE);

        return new JsonResponse(['message' => 'Validation passed with status: ' . $task->getStatus()]);
    } catch (\InvalidArgumentException $e) {
        return new JsonResponse(['error' => $e->getMessage()], 400);
    }
}
}
