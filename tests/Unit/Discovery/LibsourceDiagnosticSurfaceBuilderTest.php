<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Service\Discovery\Libsource\LibsourceDiagnosticSurfaceBuilder;
use App\Discovering\Service\Discovery\Source\CategoryDiscoverySourceProvider;
use App\Discovering\Service\Discovery\Source\DocumentDiscoverySourceProvider;
use App\Discovering\Service\Discovery\Source\OfferingDiscoverySourceProvider;
use App\Discovering\Service\Discovery\Source\ProjectDiscoverySourceProvider;
use App\Discovering\Service\Discovery\Source\Repository\CategoryDiscoverySourceRecordRepository;
use App\Discovering\Service\Discovery\Source\Repository\DiscoverySourceRepositoryRegistry;
use App\Discovering\Service\Discovery\Source\Repository\DocumentDiscoverySourceRecordRepository;
use App\Discovering\Service\Discovery\Source\Repository\OfferingDiscoverySourceRecordRepository;
use App\Discovering\Service\Discovery\Source\Repository\ProjectDiscoverySourceRecordRepository;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the libsource diagnostic surface builder test case for the Discovering component.
 */
final class LibsourceDiagnosticSurfaceBuilderTest extends TestCase
{
    public function testItBuildsRepositoryAwareDiagnosticEntries(): void
    {
        $providers = [
            new ProjectDiscoverySourceProvider(new ProjectDiscoverySourceRecordRepository()),
            new OfferingDiscoverySourceProvider(new OfferingDiscoverySourceRecordRepository()),
            new DocumentDiscoverySourceProvider(new DocumentDiscoverySourceRecordRepository()),
            new CategoryDiscoverySourceProvider(new CategoryDiscoverySourceRecordRepository()),
        ];

        $registry = new DiscoverySourceRepositoryRegistry([
            new ProjectDiscoverySourceRecordRepository(),
            new OfferingDiscoverySourceRecordRepository(),
            new DocumentDiscoverySourceRecordRepository(),
            new CategoryDiscoverySourceRecordRepository(),
        ]);

        $surface = (new LibsourceDiagnosticSurfaceBuilder($providers, $registry))->build();

        self::assertCount(4, $surface->entries);
        self::assertSame('category-source-provider', $surface->entries[0]->sourceName);
        self::assertSame('category', $surface->entries[0]->resourceType);
        self::assertSame(CategoryDiscoverySourceProvider::class, $surface->entries[0]->providerClass);
        self::assertSame(CategoryDiscoverySourceRecordRepository::class, $surface->entries[0]->repositoryClass);
        self::assertSame(2, $surface->entries[0]->recordCount);
    }
}
