<?php

declare(strict_types=1);

namespace App\Repository\Discovery;

use App\Entity\Discovery\DiscoveryIndexDocumentEntity;
use App\RepositoryInterface\Discovery\DiscoveryIndexDocumentRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DiscoveryIndexDocumentEntity>
 */
final class DiscoveryIndexDocumentRepository extends ServiceEntityRepository implements DiscoveryIndexDocumentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiscoveryIndexDocumentEntity::class);
    }

    public function save(DiscoveryIndexDocumentEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DiscoveryIndexDocumentEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
