<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Service\Discovery\Libsource\LibsourceDiagnosticSurfaceBuilder;
use App\Discovering\Service\Discovery\Libsource\LibsourceManagementActionService;
use App\Discovering\Service\Discovery\Libsource\Log\EphemeralLibsourceOperatorEventLogStore;
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
 * Exercises the libsource management action service test case for the Discovering component.
 */
final class LibsourceManagementActionServiceTest extends TestCase
{
    private function createService(EphemeralLibsourceOperatorEventLogStore $store): LibsourceManagementActionService
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

        return new LibsourceManagementActionService(
            diagnosticSurfaceBuilder: new LibsourceDiagnosticSurfaceBuilder($providers, $registry),
            eventLogStore: $store,
        );
    }

    public function testAuditAlignmentProducesSummaryAndAppendsEvent(): void
    {
        $store = new EphemeralLibsourceOperatorEventLogStore();
        $service = $this->createService($store);

        $result = $service->auditAlignment();

        self::assertSame('audit-alignment', $result->actionName);
        self::assertSame('Audited 4 libsource entries covering 8 records.', $result->summary);
        self::assertSame(1, count($store->all()));
        self::assertSame('action:audit-alignment', $store->all()[0]->eventName);
    }

    public function testInspectReturnsPayloadForKnownSourceAndLogsIt(): void
    {
        $store = new EphemeralLibsourceOperatorEventLogStore();
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
        $store = new EphemeralLibsourceOperatorEventLogStore();
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
