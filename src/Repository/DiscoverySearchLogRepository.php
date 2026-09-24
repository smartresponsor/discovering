<?php

declare(strict_types=1);

namespace App\Discovering\Repository;

use App\Discovering\Entity\DiscoverySearchLogEntity;
use App\Discovering\RepositoryInterface\DiscoverySearchLogRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DiscoverySearchLogEntity>
 */
final class DiscoverySearchLogRepository extends ServiceEntityRepository implements DiscoverySearchLogRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiscoverySearchLogEntity::class);
    }

    public function save(DiscoverySearchLogEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DiscoverySearchLogEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
