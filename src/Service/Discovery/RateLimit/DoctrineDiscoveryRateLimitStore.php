<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\RateLimit;

use App\Discovering\Entity\Discovery\DiscoveryRateLimitBucketEntity;
use App\Discovering\ServiceInterface\Discovery\RateLimit\DiscoveryRateLimitStoreInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Stores Discovering rate-limit buckets through Doctrine for durable request-throttling state.
 */
final class DoctrineDiscoveryRateLimitStore implements DiscoveryRateLimitStoreInterface
{
    private bool $schemaReady = false;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function increment(string $scope, string $actorKey, int $windowSeconds): array
    {
        $this->ensureSchema();

        $bucket = $scope.'|'.$actorKey;
        $windowSeconds = max(1, $windowSeconds);
        $now = time();

        return (array) $this->entityManager->wrapInTransaction(function (EntityManagerInterface $entityManager) use ($bucket, $now, $windowSeconds): array {
            /** @var DiscoveryRateLimitBucketEntity|null $entity */
            $entity = $entityManager->getRepository(DiscoveryRateLimitBucketEntity::class)->findOneBy(['bucket' => $bucket]);

            if (null === $entity) {
                $resetAt = $now + $windowSeconds;
                $entity = new DiscoveryRateLimitBucketEntity($bucket, 1, $resetAt);
                $entityManager->persist($entity);

                return ['count' => 1, 'resetAt' => $resetAt];
            }

            if ($entity->getResetAt() <= $now) {
                $resetAt = $now + $windowSeconds;
                $entity->reset($resetAt);

                return ['count' => 1, 'resetAt' => $resetAt];
            }

            $entity->increment($entity->getResetAt());

            return [
                'count' => $entity->getBucketCount(),
                'resetAt' => $entity->getResetAt(),
            ];
        });
    }

    private function ensureSchema(): void
    {
        if ($this->schemaReady) {
            return;
        }

        $tool = new SchemaTool($this->entityManager);
        $tool->updateSchema($this->entityManager->getMetadataFactory()->getAllMetadata());
        $this->schemaReady = true;
    }
}
