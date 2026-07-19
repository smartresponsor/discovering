<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Adapter;

use App\Discovering\Entity\Discovery\DiscoveryIndexAliasEntity;
use App\Discovering\Entity\Discovery\DiscoveryIndexDocumentEntity;
use App\Discovering\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use App\Discovering\ServiceInterface\Discovery\Rebuild\DiscoveryStagingCapableAdapterInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Implements the discovery index adapter used by the discovery runtime.
 */
final class SqliteFtsDiscoveryAdapter implements DiscoveryAdapterInterface, DiscoveryStagingCapableAdapterInterface
{
    private bool $schemaReady = false;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Performs the upsert operation for this discovery service.
     *
     * @param array<string, mixed> $document
     */
    public function upsert(string $resource, string $id, array $document): void
    {
        $this->ensureSchema();
        $index = $this->normalizeIndexName($this->resolveActiveIndex($resource));

        $entity = $this->findDocument($index, $id) ?? new DiscoveryIndexDocumentEntity();
        $entity->setIndexName($index);
        $entity->setDocumentId($id);
        $entity->setTitle((string) ($document['title'] ?? ''));
        $entity->setResource((string) ($document['resource'] ?? $resource));
        $entity->setReference((string) ($document['reference'] ?? ''));
        $entity->setStatus((string) ($document['status'] ?? ''));
        $entity->setContent((string) ($document['content'] ?? ''));
        $entity->setUpdatedAt(gmdate(DATE_ATOM));

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /**
     * Performs the remove operation for this discovery service.
     */
    public function remove(string $resource, string $id): void
    {
        $this->ensureSchema();
        $index = $this->normalizeIndexName($this->resolveActiveIndex($resource));

        $this->entityManager->createQueryBuilder()
            ->delete(DiscoveryIndexDocumentEntity::class, 'd')
            ->where('d.indexName = :indexName')
            ->andWhere('d.documentId = :documentId')
            ->setParameter('indexName', $index)
            ->setParameter('documentId', $id)
            ->getQuery()
            ->execute();
    }

    /**
     * Executes the search workflow against the active discovery source or backend.
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $resource, string $query, int $limit = 20, int $offset = 0): array
    {
        $this->ensureSchema();
        $index = $this->normalizeIndexName($this->resolveActiveIndex($resource));

        $documents = $this->entityManager->createQueryBuilder()
            ->select('d')
            ->from(DiscoveryIndexDocumentEntity::class, 'd')
            ->where('d.indexName = :indexName')
            ->setParameter('indexName', $index)
            ->orderBy('d.updatedAt', 'DESC')
            ->addOrderBy('d.documentId', 'DESC')
            ->getQuery()
            ->getResult();

        $payloads = [];
        foreach ($documents as $document) {
            if (!$document instanceof DiscoveryIndexDocumentEntity) {
                continue;
            }

            $payload = [
                'id' => $document->documentId(),
                'title' => $document->title(),
                'resource' => $document->resource(),
                'reference' => $document->reference(),
                'status' => $document->status(),
                'content' => $document->content(),
                'ftsScore' => null,
                'updatedAt' => $document->updatedAt(),
            ];

            $payload['ftsScore'] = $this->scoreDocument($payload, $query);
            if ('' !== $query && null === $payload['ftsScore']) {
                continue;
            }

            $payloads[] = $payload;
        }

        if ('' !== $query) {
            usort($payloads, static function (array $left, array $right): int {
                $leftScore = (float) ($left['ftsScore'] ?? 0.0);
                $rightScore = (float) ($right['ftsScore'] ?? 0.0);
                if ($leftScore === $rightScore) {
                    return [$right['updatedAt'] ?? '', $right['id'] ?? ''] <=> [$left['updatedAt'] ?? '', $left['id'] ?? ''];
                }

                return $rightScore <=> $leftScore;
            });
        }

        return array_slice(array_values(array_map(static function (array $payload): array {
            unset($payload['updatedAt']);

            return $payload;
        }, $payloads)), $offset, $limit);
    }

    /**
     * Performs the create index operation for this discovery service.
     */
    public function createIndex(string $resource): void
    {
        $this->ensureSchema();
        $this->resolveActiveIndex($resource);
    }

    /**
     * Performs the swap alias operation for this discovery service.
     */
    public function swapAlias(string $from, string $to): void
    {
        $alias = $this->normalizeIndexName($from);
        $target = $this->normalizeIndexName($to);
        $this->ensureSchema();

        $entity = $this->findAlias($alias) ?? new DiscoveryIndexAliasEntity();
        $entity->setAlias($alias);
        $entity->setTarget($target);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /**
     * Returns the backend nameEntity value exposed by this service.
     */
    public function getBackendName(): string
    {
        return 'sqlite-fts5';
    }

    /**
     * Performs the supports staged rebuild operation for this discovery service.
     */
    public function supportsStagedRebuild(): bool
    {
        return true;
    }

    private function ensureSchema(): void
    {
        if ($this->schemaReady) {
            return;
        }

        $tool = new SchemaTool($this->entityManager);
        $tool->updateSchema([
            $this->entityManager->getClassMetadata(DiscoveryIndexDocumentEntity::class),
            $this->entityManager->getClassMetadata(DiscoveryIndexAliasEntity::class),
        ]);

        $this->schemaReady = true;
    }

    private function resolveActiveIndex(string $resource): string
    {
        $resolved = $this->normalizeIndexName($resource);

        for ($i = 0; $i < 8; ++$i) {
            $alias = $this->findAlias($resolved);
            if (null === $alias) {
                return $resolved;
            }

            $target = $alias->target();
            if ('' === $target || $target === $resolved) {
                return $resolved;
            }

            $resolved = $this->normalizeIndexName($target);
        }

        return $resolved;
    }

    private function findAlias(string $alias): ?DiscoveryIndexAliasEntity
    {
        $entity = $this->entityManager->find(DiscoveryIndexAliasEntity::class, $alias);

        return $entity instanceof DiscoveryIndexAliasEntity ? $entity : null;
    }

    private function findDocument(string $index, string $documentId): ?DiscoveryIndexDocumentEntity
    {
        $entity = $this->entityManager->getRepository(DiscoveryIndexDocumentEntity::class)->findOneBy([
            'indexName' => $index,
            'documentId' => $documentId,
        ]);

        return $entity instanceof DiscoveryIndexDocumentEntity ? $entity : null;
    }

    /**
     * @param array<string, mixed> $document
     */
    private function scoreDocument(array $document, string $query): ?float
    {
        $query = trim($query);
        if ('' === $query) {
            return null;
        }

        $tokens = array_values(array_filter(preg_split('/[^a-z0-9]+/i', strtolower($query)) ?: [], static fn (string $token): bool => '' !== $token));
        if ([] === $tokens) {
            return null;
        }

        $haystack = strtolower(trim(implode(' ', [
            (string) ($document['title'] ?? ''),
            (string) ($document['reference'] ?? ''),
            (string) ($document['resource'] ?? ''),
            (string) ($document['status'] ?? ''),
            (string) ($document['content'] ?? ''),
        ])));

        $matchCount = 0;
        foreach ($tokens as $token) {
            if (str_contains($haystack, $token)) {
                ++$matchCount;
            }
        }

        if (0 === $matchCount) {
            return null;
        }

        return -1.0 * (float) $matchCount;
    }

    private function normalizeIndexName(string $resource): string
    {
        $normalized = preg_replace('/[^a-z0-9_]+/i', '_', strtolower($resource)) ?: 'global';

        return trim($normalized, '_') ?: 'global';
    }
}
