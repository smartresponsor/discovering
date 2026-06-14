<?php

declare(strict_types=1);

namespace App\Repository\Discovery;

use App\Entity\Discovery\DiscoveryFeedbackEntity;
use App\RepositoryInterface\Discovery\DiscoveryFeedbackRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DiscoveryFeedbackEntity>
 */
final class DiscoveryFeedbackRepository extends ServiceEntityRepository implements DiscoveryFeedbackRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiscoveryFeedbackEntity::class);
    }

    public function save(DiscoveryFeedbackEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DiscoveryFeedbackEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
