<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Libsource\LibsourceDiagnosticSurfaceBuilder;
use App\Service\Discovery\Libsource\LibsourceManagementActionService;
use App\Service\Discovery\Libsource\LibsourceManagementSurfaceActionResolver;
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
use Symfony\Component\HttpFoundation\Request;


/**
 * Exercises the libsource management surface action resolver test case for the Discovering component.
 */
final class LibsourceManagementSurfaceActionResolverTest extends TestCase
{
    private function createResolver(EphemeralLibsourceOperatorEventLogStore $store): LibsourceManagementSurfaceActionResolver
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

        $service = new LibsourceManagementActionService(
            diagnosticSurfaceBuilder: new LibsourceDiagnosticSurfaceBuilder($providers, $registry),
            eventLogStore: $store,
        );

        return new LibsourceManagementSurfaceActionResolver($service);
    }

    public function testItResolvesInspectAction(): void
    {
        $store = new EphemeralLibsourceOperatorEventLogStore();
        $resolver = $this->createResolver($store);

        $result = $resolver->resolve(new Request(query: ['action' => 'inspect', 'sourceName' => 'category-source-provider']));

        self::assertNotNull($result);
        self::assertSame('inspect', $result->actionName);
        self::assertSame('category-source-provider', $result->payload['sourceName']);
        self::assertCount(1, $store->all());
    }

    public function testItResolvesClearEventLogAction(): void
    {
        $store = new EphemeralLibsourceOperatorEventLogStore();
        $resolver = $this->createResolver($store);

        $resolver->resolve(new Request(query: ['action' => 'audit-alignment']));
        self::assertCount(1, $store->all());

        $result = $resolver->resolve(new Request(query: ['action' => 'clear-event-log']));

        self::assertNotNull($result);
        self::assertSame('clear-event-log', $result->actionName);
        self::assertCount(0, $store->all());
    }
}
