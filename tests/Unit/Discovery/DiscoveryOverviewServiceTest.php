<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Factory\Document\DiscoveryDocumentFactory;
use App\Discovering\Provider\Document\DiscoveryDocumentProvider;
use App\Discovering\Provider\Source\DiscoveryCategorySourceProvider;
use App\Discovering\Provider\Source\DiscoveryDocumentSourceProvider;
use App\Discovering\Provider\Source\DiscoveryOfferingSourceProvider;
use App\Discovering\Provider\Source\DiscoveryProjectSourceProvider;
use App\Discovering\Repository\Source\DiscoveryCategorySourceRecordRepository;
use App\Discovering\Repository\Source\DiscoveryDocumentSourceRecordRepository;
use App\Discovering\Repository\Source\DiscoveryOfferingSourceRecordRepository;
use App\Discovering\Repository\Source\DiscoveryProjectSourceRecordRepository;
use App\Discovering\Service\Overview\DiscoveryOverviewService;
use App\Discovering\ServiceInterface\Backend\DiscoveryBackendInterface;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the discovery overview service test case for the Discovering component.
 */
final class DiscoveryOverviewServiceTest extends TestCase
{
    public function testItBuildsCountsBySourceName(): void
    {
        $sourceProviders = [
            new DiscoveryProjectSourceProvider(new DiscoveryProjectSourceRecordRepository()),
            new DiscoveryOfferingSourceProvider(new DiscoveryOfferingSourceRecordRepository()),
            new DiscoveryDocumentSourceProvider(new DiscoveryDocumentSourceRecordRepository()),
            new DiscoveryCategorySourceProvider(new DiscoveryCategorySourceRecordRepository()),
        ];

        $documentProvider = new DiscoveryDocumentProvider(
            sourceProviders: $sourceProviders,
            documentFactory: new DiscoveryDocumentFactory(),
        );

        $service = new DiscoveryOverviewService(
            documentProvider: $documentProvider,
            adapter: new class implements DiscoveryBackendInterface {
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
