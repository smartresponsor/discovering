<?php

declare(strict_types=1);

namespace App\Discovering\Repository\Feedback;

use App\Discovering\Entity\DiscoveryFeedbackEntity;
use App\Discovering\ServiceInterface\DiscoveryFeedbackStoreInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Stores Discovering feedback records through Doctrine when a Doctrine entity manager is available.
 */
final class DiscoveryDoctrineFeedbackStore implements DiscoveryFeedbackStoreInterface
{
    private bool $schemaReady = false;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function recordClick(string $resource, string $hitId, string $title = '', string $reference = ''): int
    {
        return (int) $this->entityManager->wrapInTransaction(function (EntityManagerInterface $entityManager) use ($resource, $hitId, $title, $reference): int {
            $this->ensureSchema();

            /** @var DiscoveryFeedbackEntity|null $entity */
            $entity = $entityManager->getRepository(DiscoveryFeedbackEntity::class)->findOneBy([
                'resource' => $resource,
                'hitId' => $hitId,
            ]);

            $lastClickedAt = gmdate(DATE_ATOM);
            if (null === $entity) {
                $entity = new DiscoveryFeedbackEntity($resource, $hitId, $title, $reference, 1, $lastClickedAt);
                $entityManager->persist($entity);

                return 1;
            }

            $entity->increment($title, $reference, $lastClickedAt);

            return $entity->getClickCount();
        });
    }

    public function getClickCount(string $resource, string $hitId): int
    {
        $this->ensureSchema();

        /** @var DiscoveryFeedbackEntity|null $entity */
        $entity = $this->entityManager->getRepository(DiscoveryFeedbackEntity::class)->findOneBy([
            'resource' => $resource,
            'hitId' => $hitId,
        ]);

        return $entity?->getClickCount() ?? 0;
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
