<?php

declare(strict_types=1);

namespace App\Discovering\Repository;

use App\Discovering\Entity\DiscoveryLibsourceOperatorEventEntity;
use App\Discovering\RepositoryInterface\DiscoveryLibsourceOperatorEventRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DiscoveryLibsourceOperatorEventEntity>
 */
final class DiscoveryLibsourceOperatorEventRepository extends ServiceEntityRepository implements DiscoveryLibsourceOperatorEventRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiscoveryLibsourceOperatorEventEntity::class);
    }

    public function save(DiscoveryLibsourceOperatorEventEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DiscoveryLibsourceOperatorEventEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
