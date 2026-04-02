<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Adapter\SqliteFtsDiscoveryAdapter;
use App\Service\Discovery\Document\DiscoveryDocumentProvider;
use App\Service\Discovery\Document\DiscoveryDocumentFactory;
use App\Service\Discovery\Overview\DiscoveryOverviewService;
use App\Service\Discovery\Source\CategoryDiscoverySourceProvider;
use App\Service\Discovery\Source\DocumentDiscoverySourceProvider;
use App\Service\Discovery\Source\OfferingDiscoverySourceProvider;
use App\Service\Discovery\Source\ProjectDiscoverySourceProvider;
use App\Service\Discovery\Source\Repository\CategoryDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\DocumentDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\OfferingDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\ProjectDiscoverySourceRecordRepository;
use App\Service\Discovery\Support\DiscoveryDocumentMatcher;
use PHPUnit\Framework\TestCase;

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
            adapter: new SqliteFtsDiscoveryAdapter($documentProvider, new DiscoveryDocumentMatcher()),
            sourceProviders: $sourceProviders,
        );

        $overview = $service->buildOverview();

        self::assertSame(8, $overview->totalDocuments);
        self::assertSame(2, $overview->countsBySourceName['project-source-provider']);
        self::assertSame(2, $overview->countsBySourceName['offering-source-provider']);
        self::assertSame(2, $overview->countsBySourceName['document-source-provider']);
        self::assertSame(2, $overview->countsBySourceName['category-source-provider']);
    }
}
