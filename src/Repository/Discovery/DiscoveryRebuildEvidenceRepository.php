<?php

declare(strict_types=1);

namespace App\Repository\Discovery;

use App\Entity\Discovery\DiscoveryRebuildEvidenceEntity;
use App\RepositoryInterface\Discovery\DiscoveryRebuildEvidenceRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DiscoveryRebuildEvidenceEntity>
 */
final class DiscoveryRebuildEvidenceRepository extends ServiceEntityRepository implements DiscoveryRebuildEvidenceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiscoveryRebuildEvidenceEntity::class);
    }

    public function save(DiscoveryRebuildEvidenceEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DiscoveryRebuildEvidenceEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
