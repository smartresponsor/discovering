<?php

declare(strict_types=1);

namespace App\Repository\Discovery;

use App\Entity\Discovery\DiscoveryRateLimitBucketEntity;
use App\RepositoryInterface\Discovery\DiscoveryRateLimitBucketRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DiscoveryRateLimitBucketEntity>
 */
final class DiscoveryRateLimitBucketRepository extends ServiceEntityRepository implements DiscoveryRateLimitBucketRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiscoveryRateLimitBucketEntity::class);
    }

    public function save(DiscoveryRateLimitBucketEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DiscoveryRateLimitBucketEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
