<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\LibsourceManagementActionResult;
use App\Service\Discovery\Libsource\LibsourceDiagnosticSurfaceBuilder;
use App\Service\Discovery\Libsource\LibsourceOperatorEventTrailBuilder;
use App\Service\Discovery\Libsource\Log\EphemeralLibsourceOperatorEventLogStore;
use App\Service\Discovery\Source\CategoryDiscoverySourceProvider;
use App\Service\Discovery\Source\DocumentDiscoverySourceProvider;
use App\Service\Discovery\Source\OfferingDiscoverySourceProvider;
use App\Service\Discovery\Source\ProjectDiscoverySourceProvider;
use App\Service\Discovery\Source\Repository\CategoryDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\DiscoverySourceRepositoryRegistry;
use App\Service\Discovery\Source\Repository\DocumentDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\OfferingDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\ProjectDiscoverySourceRecordRepository;
use PHPUnit\Framework\TestCase;


/**
 * Exercises the libsource operator event trail builder test case for the Discovering component.
 */
final class LibsourceOperatorEventTrailBuilderTest extends TestCase
{
    public function testItIncludesSurfaceLoadStoredEventsAndCoverageEvents(): void
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

        $store = new EphemeralLibsourceOperatorEventLogStore();
        $store->append(new \App\Dto\Discovery\LibsourceOperatorEvent('action:stored', 'warning', 'Stored event'));

        $builder = new LibsourceOperatorEventTrailBuilder(
            diagnosticSurfaceBuilder: new LibsourceDiagnosticSurfaceBuilder($providers, $registry),
            eventLogStore: $store,
        );

        $events = $builder->build(new LibsourceManagementActionResult('inspect', 'Inspected source.', ['sourceName' => 'project-source-provider']));

        self::assertSame('surface:load', $events[0]->eventName);
        self::assertStringContainsString('4 diagnostic entries', $events[0]->summary);
        self::assertSame('action:inspect', $events[1]->eventName);
        self::assertSame('Stored event', $events[2]->summary);
        self::assertSame('coverage:source', $events[3]->eventName);
        self::assertSame(7, count($events));
    }
}
