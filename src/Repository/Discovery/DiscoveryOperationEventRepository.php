<?php

declare(strict_types=1);

namespace App\Discovering\Repository\Discovery;

use App\Discovering\Entity\Discovery\DiscoveryOperationEventEntity;
use App\Discovering\RepositoryInterface\Discovery\DiscoveryOperationEventRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DiscoveryOperationEventEntity>
 */
final class DiscoveryOperationEventRepository extends ServiceEntityRepository implements DiscoveryOperationEventRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiscoveryOperationEventEntity::class);
    }

    public function save(DiscoveryOperationEventEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DiscoveryOperationEventEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
