<?php

declare(strict_types=1);

namespace App\Tests\Contract\Discovery;

use App\Service\Discovery\Http\DiscoveryApiContract;
use App\Tests\Functional\Discovery\AbstractDiscoveryWebTestCase;


/**
 * Exercises the discovery management export contract test case for the Discovering component.
 */
final class DiscoveryManagementExportContractTest extends AbstractDiscoveryWebTestCase
{
    use DiscoveryApiContractAssertions;

    public function testOverviewExportRespectsEnvelopeContract(): void
    {
        $client = $this->createDiscoveryClient($this->managementTokenServer());
        $client->request('GET', '/management/discovery/export', [], [], $this->managementTokenServer());

        self::assertResponseIsSuccessful();
        self::assertSame(DiscoveryApiContract::ENVELOPE_SCHEMA_FAMILY, $client->getResponse()->headers->get(DiscoveryApiContract::SCHEMA_FAMILY_HEADER));

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertDiscoveryEnvelopeContract($payload);
        self::assertArrayHasKey('backendName', $payload['data']);
        self::assertArrayHasKey('countsByResourceType', $payload['data']);
    }

    public function testOperationsExportRespectsEnvelopeContract(): void
    {
        $queryClient = $this->createDiscoveryClient();
        $queryClient->request('GET', '/api/v1/discovery', [
            'query' => 'governance',
            'resource' => 'briefing',
        ]);

        $client = $this->createDiscoveryClient($this->managementTokenServer());
        $client->request('GET', '/management/discovery/operations/export', [], [], $this->managementTokenServer());

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertDiscoveryEnvelopeContract($payload);
        self::assertIsArray($payload['data']);
        self::assertNotEmpty($payload['data']);
    }

    public function testPlatformProbesExportRespectsEnvelopeContract(): void
    {
        $client = $this->createDiscoveryClient($this->managementTokenServer());
        $client->request('GET', '/management/discovery/platform/probes/export', [], [], $this->managementTokenServer());

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertDiscoveryEnvelopeContract($payload, 'discovery.platform.probes');
        self::assertArrayHasKey('performedProbeCount', $payload['data']);
        self::assertArrayHasKey('overallStatus', $payload['data']);
        self::assertArrayHasKey('recommendedAction', $payload['data']);
        self::assertArrayHasKey('probes', $payload['data']);
    }

}
