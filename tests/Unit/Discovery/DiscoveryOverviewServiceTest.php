<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Document\DiscoveryDocumentFactory;
use App\Service\Discovery\Document\DiscoveryDocumentProvider;
use App\Service\Discovery\Overview\DiscoveryOverviewService;
use App\Service\Discovery\Source\CategoryDiscoverySourceProvider;
use App\Service\Discovery\Source\DocumentDiscoverySourceProvider;
use App\Service\Discovery\Source\OfferingDiscoverySourceProvider;
use App\Service\Discovery\Source\ProjectDiscoverySourceProvider;
use App\Service\Discovery\Source\Repository\CategoryDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\DocumentDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\OfferingDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\ProjectDiscoverySourceRecordRepository;
use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use PHPUnit\Framework\TestCase;


/**
 * Exercises the discovery overview service test case for the Discovering component.
 */
final class DiscoveryOverviewServiceTest extends TestCase
{
    public function testItBuildsCountsBySourceName(): void
    {
        $sourceProviders = [
            new ProjectDiscoverySourceProvider(new ProjectDiscoverySourceRecordRepository()),
            new OfferingDiscoverySourceProvider(new OfferingDiscoverySourceRecordRepository()),
            new DocumentDiscoverySourceProvider(new DocumentDiscoverySourceRecordRepository()),
            new CategoryDiscoverySourceProvider(new CategoryDiscoverySourceRecordRepository()),
        ];

        $documentProvider = new DiscoveryDocumentProvider(
            sourceProviders: $sourceProviders,
            documentFactory: new DiscoveryDocumentFactory(),
        );

        $service = new DiscoveryOverviewService(
            documentProvider: $documentProvider,
            adapter: new class implements DiscoveryAdapterInterface {
                public function upsert(string $resource, string $id, array $document): void
                {
                }

                public function remove(string $resource, string $id): void
                {
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
            },
            sourceProviders: $sourceProviders,
        );

        $overview = $service->buildOverview();

        self::assertSame('test-backend', $overview->backendName);
        self::assertSame(8, $overview->totalDocuments);
        self::assertSame(2, $overview->countsBySourceName['project-source-provider']);
        self::assertSame(2, $overview->countsBySourceName['offering-source-provider']);
        self::assertSame(2, $overview->countsBySourceName['document-source-provider']);
        self::assertSame(2, $overview->countsBySourceName['category-source-provider']);
        self::assertSame(2, $overview->countsByResourceType['project']);
        self::assertSame(2, $overview->countsByResourceType['offering']);
        self::assertSame(2, $overview->countsByResourceType['document']);
        self::assertSame(2, $overview->countsByResourceType['category']);
    }
}
