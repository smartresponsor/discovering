<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\ReindexRequest;
use App\Service\Discovery\Indexer\DiscoveryIndexer;
use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use App\ServiceInterface\Discovery\Document\DiscoveryDocumentProviderInterface;
use App\ValueObject\Discovery\DiscoveryDocument;
use PHPUnit\Framework\TestCase;

final class DiscoveryIndexerTest extends TestCase
{
    public function testItRebuildsGlobalAndResourceIndexesFromDocumentProvider(): void
    {
        $operations = [];
        $adapter = new class($operations) implements DiscoveryAdapterInterface {
            /** @var list<array<string, mixed>> */
            private array $operations;

            /** @param list<array<string, mixed>> $operations */
            public function __construct(array &$operations)
            {
                $this->operations = &$operations;
            }

            public function upsert(string $resource, string $id, array $document): void
            {
                $this->operations[] = ['type' => 'upsert', 'resource' => $resource, 'id' => $id];
            }

            public function remove(string $resource, string $id): void
            {
                $this->operations[] = ['type' => 'remove', 'resource' => $resource, 'id' => $id];
            }

            public function search(string $resource, string $query, int $limit = 20, int $offset = 0): array
            {
                return [];
            }

            public function createIndex(string $resource): void
            {
                $this->operations[] = ['type' => 'createIndex', 'resource' => $resource];
            }

            public function swapAlias(string $from, string $to): void
            {
            }

            public function getBackendName(): string
            {
                return 'test-backend';
            }
        };

        $documentProvider = new class implements DiscoveryDocumentProviderInterface {
            public function provide(): array
            {
                return [
                    new DiscoveryDocument(
                        id: 'briefing-1',
                        resource: 'briefing',
                        title: 'Briefing 1',
                        reference: 'briefing-1',
                        status: 'active',
                        content: 'Briefing body',
                    ),
                    new DiscoveryDocument(
                        id: 'playbook-1',
                        resource: 'playbook',
                        title: 'Playbook 1',
                        reference: 'playbook-1',
                        status: 'active',
                        content: 'Playbook body',
                    ),
                ];
            }
        };

        $indexer = new DiscoveryIndexer($adapter, $documentProvider);
        $summary = $indexer->rebuild(new ReindexRequest(resource: 'briefing'));

        self::assertSame([
            ['type' => 'createIndex', 'resource' => 'global'],
            ['type' => 'createIndex', 'resource' => 'briefing'],
            ['type' => 'upsert', 'resource' => 'global', 'id' => 'briefing-1'],
            ['type' => 'upsert', 'resource' => 'briefing', 'id' => 'briefing-1'],
        ], $operations);
        self::assertSame('briefing', $summary->resource);
        self::assertSame('full', $summary->rebuildMode);
        self::assertSame('test-backend', $summary->backendName);
        self::assertFalse($summary->zeroDowntimeReady);
        self::assertSame(2, $summary->candidateDocumentCount);
        self::assertSame(1, $summary->indexedDocumentCount);
        self::assertSame(1, $summary->skippedDocumentCount);
        self::assertSame(['global' => 1, 'briefing' => 1], $summary->indexedCountsByResource);
        self::assertStringStartsWith('reb-', $summary->evidenceId);
    }

    public function testItRemovesFromResourceAndGlobalIndexes(): void
    {
        $operations = [];
        $adapter = new class($operations) implements DiscoveryAdapterInterface {
            /** @var list<array<string, mixed>> */
            private array $operations;

            /** @param list<array<string, mixed>> $operations */
            public function __construct(array &$operations)
            {
                $this->operations = &$operations;
            }

            public function upsert(string $resource, string $id, array $document): void
            {
            }

            public function remove(string $resource, string $id): void
            {
                $this->operations[] = ['resource' => $resource, 'id' => $id];
            }

            public function search(string $resource, string $query, int $limit = 20, int $offset = 0): array
            {
                return [];
            }

            public function createIndex(string $resource): void
            {
            }

            public function swapAlias(string $from, string $to): void
            {
            }

            public function getBackendName(): string
            {
                return 'test-backend';
            }
        };

        $documentProvider = new class implements DiscoveryDocumentProviderInterface {
            public function provide(): array
            {
                return [];
            }
        };

        $indexer = new DiscoveryIndexer($adapter, $documentProvider);
        $indexer->remove('briefing', 'briefing-1');

        self::assertSame([
            ['resource' => 'briefing', 'id' => 'briefing-1'],
            ['resource' => 'global', 'id' => 'briefing-1'],
        ], $operations);
    }
}
