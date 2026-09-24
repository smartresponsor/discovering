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
use App\Discovering\Service\Libsource\DiscoveryLibsourceManagementActionService;
use App\Discovering\Service\Libsource\Log\DiscoveryEphemeralLibsourceOperatorEventLogStore;
use App\Discovering\Service\Source\Repository\DiscoverySourceRepositoryRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the libsource management action service test case for the Discovering component.
 */
final class LibsourceManagementActionServiceTest extends TestCase
{
    private function createService(DiscoveryEphemeralLibsourceOperatorEventLogStore $store): DiscoveryLibsourceManagementActionService
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

        return new DiscoveryLibsourceManagementActionService(
            diagnosticSurfaceBuilder: new DiscoveryLibsourceDiagnosticSurfaceBuilder($providers, $registry),
            eventLogStore: $store,
        );
    }

    public function testAuditAlignmentProducesSummaryAndAppendsEvent(): void
    {
        $store = new DiscoveryEphemeralLibsourceOperatorEventLogStore();
        $service = $this->createService($store);

        $result = $service->auditAlignment();

        self::assertSame('audit-alignment', $result->actionName);
        self::assertSame('Audited 4 libsource entries covering 8 records.', $result->summary);
        self::assertSame(1, count($store->all()));
        self::assertSame('action:audit-alignment', $store->all()[0]->eventName);
    }

    public function testInspectReturnsPayloadForKnownSourceAndLogsIt(): void
    {
        $store = new DiscoveryEphemeralLibsourceOperatorEventLogStore();
        $service = $this->createService($store);

        $result = $service->inspect('document-source-provider');

        self::assertSame('inspect', $result->actionName);
        self::assertSame('document-source-provider', $result->payload['sourceName']);
        self::assertSame('document', $result->payload['resourceType']);
        self::assertSame(1, count($store->all()));
        self::assertSame('action:inspect', $store->all()[0]->eventName);
    }

    public function testClearEventLogClearsStoredEvents(): void
    {
        $store = new DiscoveryEphemeralLibsourceOperatorEventLogStore();
        $service = $this->createService($store);

        $service->auditAlignment();
        $service->inspect('project-source-provider');

        self::assertCount(2, $store->all());

        $result = $service->clearEventLog();

        self::assertSame('clear-event-log', $result->actionName);
        self::assertSame(2, $result->payload['clearedCount']);
        self::assertSame([], $store->all());
    }
}
