<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Task;
use DateTime;
use DateTimeZone;

class TaskController extends AbstractController
{

    //POST
    #[Route('/api/task', name:"createTask", methods: ['POST'])]
    public function createTask(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse 
    {
        $data = $request->getContent();
        $decodedData = json_decode($data, true);

        if (empty($decodedData['title'])) {
            return new JsonResponse(['erreur' => 'La requête doit comporter une clé \'title\''], Response::HTTP_BAD_REQUEST);
        }
        if (strlen($decodedData['title'])<3 || strlen($decodedData['title'])>255) {
            return new JsonResponse(['erreur' => 'Le titre doit avoir 3 à 255 caractères'], Response::HTTP_BAD_REQUEST);
        }
        if (empty($decodedData['description'])) {
            return new JsonResponse(['erreur' => 'La requête doit comporter une clé \'description\' et ne doit pas être nulle'], Response::HTTP_BAD_REQUEST);
        }
        if (empty($decodedData['status'])) {
            return new JsonResponse(['erreur' => 'La requête doit comporter une clé \'status\''], Response::HTTP_BAD_REQUEST);
        }
        if ($decodedData['status']!="todo" && $decodedData['status']!="in_progress" && $decodedData['status']!="done") {
            return new JsonResponse(['erreur' => 'Le status doit être : todo, in_progress ou done '], Response::HTTP_BAD_REQUEST);
        }
        $dateCreation = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $task = $serializer->deserialize($data, Task::class, 'json');
        $task->setCreatedAt($dateCreation);
        $em->persist($task);
        $em->flush();

        $jsonTask = $serializer->serialize($task, 'json');
        return new JsonResponse($jsonTask, Response::HTTP_CREATED, [], true);
   }

   //GET
   #[Route('api/task', name: 'getTask', methods: ['GET'])]
    public function getTaskByTitleOrDescription(Request $request, TaskRepository  $taskRepository, SerializerInterface $serializer) : JsonResponse
    {
        $title = $request->query->get('title');
        $description = $request->query->get('description');
        $tasks = $taskRepository->searchByTitleOrDescription($title, $description);
    
        $jsonTasks = $serializer->serialize($tasks, 'json');
    
        return new JsonResponse($jsonTasks, Response::HTTP_OK, [], true);
    }

    #[Route('/api/task/perPage', name: 'getTaskPerPage', methods: ['GET'])]
    public function getTaskPerPage(Request $request, TaskRepository $taskRepository, SerializerInterface $serializer): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $tasks = $taskRepository->getPaginatedTasks($page, 10);
        $totalTasks = $taskRepository->getTotalTasks();

        $jsonTasks = $serializer->serialize($tasks, 'json');
        $response = [
            'data' => json_decode($jsonTasks, true),
            'total' => $totalTasks,
            'page' => $page,
            'total_pages' => ceil($totalTasks / 10),
        ];

        return new JsonResponse($response, Response::HTTP_OK);
    }

      
    //PUT
    #[Route('/api/task/{id}', name:"updateTask", methods: ['PUT'])]
    public function updateTask(int $id,Request $request, SerializerInterface $serializer, EntityManagerInterface $em, TaskRepository $taskRepository ): JsonResponse 
    {
        $currentTask = $taskRepository->find($id);
        $updateTask = $serializer->deserialize($request->getContent(), Task::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentTask]);
        if (empty($updateTask->getTitle())) {
            return new JsonResponse(['erreur' => 'La requête doit comporter une clé \'title\''], Response::HTTP_BAD_REQUEST);
        }
        if (strlen($updateTask->getTitle())<3 || strlen($updateTask->getTitle())>255) {
            return new JsonResponse(['erreur' => 'Le titre doit avoir 3 à 255 caractères'], Response::HTTP_BAD_REQUEST);
        }
        if (empty($updateTask->getDescription())) {
            return new JsonResponse(['erreur' => 'La requête doit comporter une clé \'description\' et ne doit pas être nulle'], Response::HTTP_BAD_REQUEST);
        }
        if (empty($updateTask->getStatus())) {
            return new JsonResponse(['erreur' => 'La requête doit comporter une clé \'status\''], Response::HTTP_BAD_REQUEST);
        }
        if ($updateTask->getStatus()!="todo" && $updateTask->getStatus()!="in_progress" && $updateTask->getStatus()!="done") {
            return new JsonResponse(['erreur' => 'Le status doit être : todo, in_progress ou done '], Response::HTTP_BAD_REQUEST);
        }
        $dateUpdate = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $updateTask->setUpdatedAt($dateUpdate);
        $em->persist($updateTask);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
   }
   

    //DELETE
    #[Route('/api/task/{id}', name: 'deleteTask', methods: ['DELETE'])]
    public function deleteTask(int $id, TaskRepository $taskRepository, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse 
    {
        $task = $taskRepository->find($id);
        if (!$task) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $em->remove($task);
        $em->flush();

        return new JsonResponse(['message' => 'Tâche supprimée avec succès.'], Response::HTTP_OK);
        
    }
}
