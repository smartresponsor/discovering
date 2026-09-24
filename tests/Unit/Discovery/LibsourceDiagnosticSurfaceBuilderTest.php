<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Builder\Libsource\DiscoveryLibsourceDiagnosticSurfaceBuilder;
use App\Discovering\Provider\Source\DiscoveryCategorySourceProvider;
use App\Discovering\Provider\Source\DiscoveryDocumentSourceProvider;
use App\Discovering\Provider\Source\DiscoveryOfferingSourceProvider;
use App\Discovering\Provider\Source\DiscoveryProjectSourceProvider;
use App\Discovering\Repository\Source\DiscoveryCategorySourceRecordRepository;
use App\Discovering\Repository\Source\DiscoveryDocumentSourceRecordRepository;
use App\Discovering\Repository\Source\DiscoveryOfferingSourceRecordRepository;
use App\Discovering\Repository\Source\DiscoveryProjectSourceRecordRepository;
use App\Discovering\Service\Source\Repository\DiscoverySourceRepositoryRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the libsource diagnostic surface builder test case for the Discovering component.
 */
final class LibsourceDiagnosticSurfaceBuilderTest extends TestCase
{
    public function testItBuildsRepositoryAwareDiagnosticEntries(): void
    {
        $providers = [
            new DiscoveryProjectSourceProvider(new DiscoveryProjectSourceRecordRepository()),
            new DiscoveryOfferingSourceProvider(new DiscoveryOfferingSourceRecordRepository()),
            new DiscoveryDocumentSourceProvider(new DiscoveryDocumentSourceRecordRepository()),
            new DiscoveryCategorySourceProvider(new DiscoveryCategorySourceRecordRepository()),
        ];

        $registry = new DiscoverySourceRepositoryRegistry([
            new DiscoveryProjectSourceRecordRepository(),
            new DiscoveryOfferingSourceRecordRepository(),
            new DiscoveryDocumentSourceRecordRepository(),
            new DiscoveryCategorySourceRecordRepository(),
        ]);

        $surface = (new DiscoveryLibsourceDiagnosticSurfaceBuilder($providers, $registry))->build();

        self::assertCount(4, $surface->entries);
        self::assertSame('category-source-provider', $surface->entries[0]->sourceName);
        self::assertSame('category', $surface->entries[0]->resourceType);
        self::assertSame(DiscoveryCategorySourceProvider::class, $surface->entries[0]->providerClass);
        self::assertSame(DiscoveryCategorySourceRecordRepository::class, $surface->entries[0]->repositoryClass);
        self::assertSame(2, $surface->entries[0]->recordCount);
    }
}
