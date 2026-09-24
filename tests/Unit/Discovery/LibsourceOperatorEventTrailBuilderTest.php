<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Builder\Libsource\DiscoveryLibsourceDiagnosticSurfaceBuilder;
use App\Discovering\Builder\Libsource\DiscoveryLibsourceOperatorEventTrailBuilder;
use App\Discovering\DTO\DiscoveryLibsourceManagementActionResultDTO;
use App\Discovering\Provider\Source\DiscoveryCategorySourceProvider;
use App\Discovering\Provider\Source\DiscoveryDocumentSourceProvider;
use App\Discovering\Provider\Source\DiscoveryOfferingSourceProvider;
use App\Discovering\Provider\Source\DiscoveryProjectSourceProvider;
use App\Discovering\Repository\Source\DiscoveryCategorySourceRecordRepository;
use App\Discovering\Repository\Source\DiscoveryDocumentSourceRecordRepository;
use App\Discovering\Repository\Source\DiscoveryOfferingSourceRecordRepository;
use App\Discovering\Repository\Source\DiscoveryProjectSourceRecordRepository;
use App\Discovering\Service\Libsource\Log\DiscoveryEphemeralLibsourceOperatorEventLogStore;
use App\Discovering\Service\Source\Repository\DiscoverySourceRepositoryRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the libsource operator event trail builder test case for the Discovering component.
 */
final class LibsourceOperatorEventTrailBuilderTest extends TestCase
{
    public function testItIncludesSurfaceLoadStoredEventsAndCoverageEvents(): void
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

        $store = new DiscoveryEphemeralLibsourceOperatorEventLogStore();
        $store->append(new \App\Discovering\DTO\DiscoveryLibsourceOperatorEventDTO('action:stored', 'warning', 'Stored event'));

        $builder = new DiscoveryLibsourceOperatorEventTrailBuilder(
            diagnosticSurfaceBuilder: new DiscoveryLibsourceDiagnosticSurfaceBuilder($providers, $registry),
            eventLogStore: $store,
        );

        $events = $builder->build(new DiscoveryLibsourceManagementActionResultDTO('inspect', 'Inspected source.', ['sourceName' => 'project-source-provider']));

        self::assertSame('surface:load', $events[0]->eventName);
        self::assertStringContainsString('4 diagnostic entries', $events[0]->summary);
        self::assertSame('action:inspect', $events[1]->eventName);
        self::assertSame('Stored event', $events[2]->summary);
        self::assertSame('coverage:source', $events[3]->eventName);
        self::assertSame(7, count($events));
    }
}
