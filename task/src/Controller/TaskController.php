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
