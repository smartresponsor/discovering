<?php

declare(strict_types=1);

namespace App\Discovering\Repository;

use App\Discovering\Entity\DiscoveryIndexAliasEntity;
use App\Discovering\RepositoryInterface\DiscoveryIndexAliasRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DiscoveryIndexAliasEntity>
 */
final class DiscoveryIndexAliasRepository extends ServiceEntityRepository implements DiscoveryIndexAliasRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiscoveryIndexAliasEntity::class);
    }

    public function save(DiscoveryIndexAliasEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DiscoveryIndexAliasEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
