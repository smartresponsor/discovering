<?php

declare(strict_types=1);

namespace App\Repository\Discovery;

use App\Entity\Discovery\LibsourceOperatorEventEntity;
use App\RepositoryInterface\Discovery\LibsourceOperatorEventRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LibsourceOperatorEventEntity>
 */
final class LibsourceOperatorEventRepository extends ServiceEntityRepository implements LibsourceOperatorEventRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LibsourceOperatorEventEntity::class);
    }

    public function save(LibsourceOperatorEventEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LibsourceOperatorEventEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
