<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }
    public function searchByTitleOrDescription(?string $title, ?string $description): array
    {
        $qb = $this->createQueryBuilder('t');

        if ($title) {
            $qb->orWhere('t.title LIKE :title')
               ->setParameter('title', '%' . $title . '%');
        }

        if ($description) {
            $qb->orWhere('t.description LIKE :description')
               ->setParameter('description', '%' . $description . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function getPaginatedTasks(int $page, int $limit): array
    {
        $query = $this->createQueryBuilder('t')
            ->orderBy('t.created_at', 'DESC') 
            ->setFirstResult(($page - 1) * $limit) 
            ->setMaxResults($limit) 
            ->getQuery();

        return $query->getResult();
    }

    public function getTotalTasks(): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
