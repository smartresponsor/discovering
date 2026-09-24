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
use App\Discovering\Resolver\Libsource\DiscoveryLibsourceManagementSurfaceActionResolver;
use App\Discovering\Service\Libsource\DiscoveryLibsourceManagementActionService;
use App\Discovering\Service\Libsource\Log\DiscoveryEphemeralLibsourceOperatorEventLogStore;
use App\Discovering\Service\Source\Repository\DiscoverySourceRepositoryRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Exercises the libsource management surface action resolver test case for the Discovering component.
 */
final class LibsourceManagementSurfaceActionResolverTest extends TestCase
{
    private function createResolver(DiscoveryEphemeralLibsourceOperatorEventLogStore $store): DiscoveryLibsourceManagementSurfaceActionResolver
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

        $service = new DiscoveryLibsourceManagementActionService(
            diagnosticSurfaceBuilder: new DiscoveryLibsourceDiagnosticSurfaceBuilder($providers, $registry),
            eventLogStore: $store,
        );

        return new DiscoveryLibsourceManagementSurfaceActionResolver($service);
    }

    public function testItResolvesInspectAction(): void
    {
        $store = new DiscoveryEphemeralLibsourceOperatorEventLogStore();
        $resolver = $this->createResolver($store);

        $result = $resolver->resolve(new Request(query: ['action' => 'inspect', 'sourceName' => 'category-source-provider']));

        self::assertNotNull($result);
        self::assertSame('inspect', $result->actionName);
        self::assertSame('category-source-provider', $result->payload['sourceName']);
        self::assertCount(1, $store->all());
    }

    public function testItResolvesClearEventLogAction(): void
    {
        $store = new DiscoveryEphemeralLibsourceOperatorEventLogStore();
        $resolver = $this->createResolver($store);

        $resolver->resolve(new Request(query: ['action' => 'audit-alignment']));
        self::assertCount(1, $store->all());

        $result = $resolver->resolve(new Request(query: ['action' => 'clear-event-log']));

        self::assertNotNull($result);
        self::assertSame('clear-event-log', $result->actionName);
        self::assertCount(0, $store->all());
    }
}
