<?php

declare(strict_types=1);

namespace App\Discovering\Repository\Rebuild;

use App\Discovering\DTO\DiscoveryRebuildSummaryDTO;
use App\Discovering\Entity\DiscoveryRebuildEvidenceEntity;
use App\Discovering\Normalizer\Rebuild\DiscoveryRebuildEvidenceJsonSerializer;
use App\Discovering\ServiceInterface\Rebuild\DiscoveryRebuildEvidenceStoreInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Stores Discovering rebuild evidence through Doctrine for durable rebuild and rollback diagnostics.
 */
final class DiscoveryDoctrineRebuildEvidenceStore implements DiscoveryRebuildEvidenceStoreInterface
{
    private bool $schemaReady = false;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function append(DiscoveryRebuildSummaryDTO $summary): void
    {
        $this->entityManager->wrapInTransaction(function (EntityManagerInterface $entityManager) use ($summary): void {
            $this->ensureSchema();

            $entityManager->persist(new DiscoveryRebuildEvidenceEntity(
                evidenceId: $summary->evidenceId,
                resource: $summary->resource,
                finishedAt: $summary->finishedAt,
                payloadJson: json_encode($summary->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            ));
        });
    }

    public function latest(int $limit = 20): array
    {
        if ($limit <= 0) {
            return [];
        }

        $this->ensureSchema();

        /** @var list<DiscoveryRebuildEvidenceEntity> $entities */
        $entities = $this->entityManager->getRepository(DiscoveryRebuildEvidenceEntity::class)->findBy([], [
            'finishedAt' => 'DESC',
            'evidenceId' => 'DESC',
        ], $limit);

        $summaries = [];
        foreach ($entities as $entity) {
            $payload = $entity->getPayloadJson();
            if ('' === $payload) {
                continue;
            }

            foreach ($this->serializer()->decode(sprintf('[%s]', $payload)) as $summary) {
                $summaries[] = $summary;
            }
        }

        return $summaries;
    }

    private function serializer(): DiscoveryRebuildEvidenceJsonSerializer
    {
        return new DiscoveryRebuildEvidenceJsonSerializer();
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
