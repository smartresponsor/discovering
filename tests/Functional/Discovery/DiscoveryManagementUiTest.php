<?php

declare(strict_types=1);

namespace App\Tests\Functional\Discovery;

use App\Service\Discovery\Http\DiscoveryJsonResponseFactory;

final class DiscoveryManagementUiTest extends AbstractDiscoveryWebTestCase
{
    public function testDiscoveryPageRendersWithSeededResult(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request('GET', '/discovery', [
            'query' => 'governance',
            'resource' => 'briefing',
            'mode' => 'governance',
        ]);

        self::assertResponseIsSuccessful();

        $content = (string) $client->getResponse()->getContent();

        self::assertStringContainsString('Discovering', $content);
        self::assertStringContainsString('Live source governance briefing', $content);
        self::assertStringContainsString('Mark useful', $content);
    }

    public function testManagementOverviewRequiresManagementToken(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request('GET', '/management/discovery');

        self::assertResponseStatusCodeSame(403);
        self::assertStringContainsString('Forbidden discovery management request.', (string) $client->getResponse()->getContent());
    }

    public function testManagementOverviewExportReturnsCoverageSummary(): void
    {
        $client = $this->createDiscoveryClient($this->managementTokenServer());
        $client->request('GET', '/management/discovery/export', [], [], $this->managementTokenServer());

        self::assertResponseIsSuccessful();
        self::assertSame(DiscoveryJsonResponseFactory::API_VERSION, $client->getResponse()->headers->get(DiscoveryJsonResponseFactory::API_VERSION_HEADER));

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($payload['ok']);
        self::assertSame(DiscoveryJsonResponseFactory::API_VERSION, $payload['apiVersion']);
        self::assertFalse($payload['meta']['deprecatedAlias']);
        self::assertSame('/management/discovery/export', $payload['meta']['canonicalPath']);
        self::assertSame('sqlite-fts5', $payload['data']['backendName']);
        self::assertGreaterThanOrEqual(1, $payload['data']['totalDocuments']);
        self::assertArrayHasKey('briefing', $payload['data']['countsByResourceType']);
        self::assertArrayHasKey('briefing-file-source-provider', $payload['data']['countsBySourceName']);
        self::assertNotEmpty($payload['data']['sampleDocuments']);
    }

    public function testManagementOperationsExportReturnsRecordedEvents(): void
    {
        $client = $this->createDiscoveryClient();
        $client->request('GET', '/api/v1/discovery', [
            'query' => 'governance',
            'resource' => 'briefing',
        ]);

        $managementClient = $this->createDiscoveryClient($this->managementTokenServer());
        $managementClient->request('GET', '/management/discovery/operations/export', [], [], $this->managementTokenServer());

        self::assertResponseIsSuccessful();
        self::assertTrue($managementClient->getResponse()->headers->has('X-Request-Id'));

        $payload = json_decode((string) $managementClient->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($payload['ok']);
        self::assertNotEmpty($payload['data']);
        self::assertContains('discovery.api.query', array_column($payload['data'], 'operation'));
    }

    public function testManagementRebuildReturnsEvidenceSummary(): void
    {
        $client = $this->createDiscoveryClient($this->managementTokenServer());
        $client->request('POST', '/management/discovery/rebuild', [], [], $this->managementTokenServer());

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($payload['ok']);
        self::assertSame('discovery.rebuild.summary', $payload['meta']['schemaFamily']);
        self::assertSame('global', $payload['data']['resource']);
        self::assertSame('staged_alias_swap', $payload['data']['deploymentMode']);
        self::assertTrue($payload['data']['zeroDowntimeReady']);
        self::assertTrue($payload['data']['aliasSwapApplied']);
        self::assertArrayHasKey('global', $payload['data']['stagedIndexes']);
        self::assertStringStartsWith('reb-', $payload['data']['evidenceId']);
    }

    public function testManagementRebuildExportReturnsRecordedEvidence(): void
    {
        $client = $this->createDiscoveryClient($this->managementTokenServer());
        $client->request('POST', '/management/discovery/rebuild', [], [], $this->managementTokenServer());

        $exportClient = $this->createDiscoveryClient($this->managementTokenServer());
        $exportClient->request('GET', '/management/discovery/rebuilds/export', [], [], $this->managementTokenServer());

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $exportClient->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($payload['ok']);
        self::assertSame('discovery.rebuild.summary.list', $payload['meta']['schemaFamily']);
        self::assertNotEmpty($payload['data']);
        self::assertSame('staged_alias_swap', $payload['data'][0]['deploymentMode']);
        self::assertTrue($payload['data'][0]['aliasSwapApplied']);
        self::assertStringStartsWith('reb-', $payload['data'][0]['evidenceId']);
    }

    public function testManagementStateTopologyExportReturnsDistributedReadinessPosture(): void
    {
        $client = $this->createDiscoveryClient($this->managementTokenServer());
        $client->request('GET', '/management/discovery/state-topology/export', [], [], $this->managementTokenServer());

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($payload['ok']);
        self::assertSame('discovery.state.topology', $payload['meta']['schemaFamily']);
        self::assertFalse($payload['data']['distributedReady']);
        self::assertSame('local_file', $payload['data']['stores'][0]['storageMode']);
        self::assertArrayHasKey('notes', $payload['data']);
    }

    public function testManagementPlatformProbesExportReturnsReachabilitySummary(): void
    {
        $client = $this->createDiscoveryClient($this->managementTokenServer());
        $client->request('GET', '/management/discovery/platform/probes/export', [], [], $this->managementTokenServer());

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($payload['ok']);
        self::assertSame('discovery.platform.probes', $payload['meta']['schemaFamily']);
        self::assertSame(0, $payload['data']['performedProbeCount']);
        self::assertSame(6, $payload['data']['skippedProbeCount']);
        self::assertCount(6, $payload['data']['probes']);
        self::assertSame('local_only', $payload['data']['probes'][0]['status']);
    }

    public function testManagementPlatformExportReturnsPlatformDiagnostics(): void
    {
        $client = $this->createDiscoveryClient($this->managementTokenServer());
        $client->request('GET', '/management/discovery/platform/export', [], [], $this->managementTokenServer());

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($payload['ok']);
        self::assertSame('discovery.platform.diagnostics', $payload['meta']['schemaFamily']);
        self::assertSame('sqlite-fts5', $payload['data']['backendName']);
        self::assertSame('sqlite', $payload['data']['indexStoreBackend']);
        self::assertTrue($payload['data']['stagedRebuildSupported']);
        self::assertFalse($payload['data']['distributedReady']);
        self::assertArrayHasKey('blockingStores', $payload['data']);
        self::assertArrayHasKey('notes', $payload['data']);
    }


    public function testManagementRollbackExportReturnsRollbackPlan(): void
    {
        $client = $this->createDiscoveryClient($this->managementTokenServer());
        $client->request('POST', '/management/discovery/rebuild', [], [], $this->managementTokenServer());
        $client->request('POST', '/management/discovery/rebuild', [], [], $this->managementTokenServer());

        $exportClient = $this->createDiscoveryClient($this->managementTokenServer());
        $exportClient->request('GET', '/management/discovery/rollback/export', [], [], $this->managementTokenServer());

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $exportClient->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($payload['ok']);
        self::assertSame('discovery.rollback.plan', $payload['meta']['schemaFamily']);
        self::assertSame('plan_ready', $payload['data']['status']);
        self::assertTrue($payload['data']['rollbackReady']);
        self::assertNotEmpty($payload['data']['currentEvidenceId']);
        self::assertNotEmpty($payload['data']['previousEvidenceId']);
        self::assertNotEmpty($payload['data']['recommendedCommand']);
    }


    public function testManagementRollbackExecutePromotesRollbackTarget(): void
    {
        $client = $this->createDiscoveryClient($this->managementTokenServer());
        $client->request('POST', '/management/discovery/rebuild', [], [], $this->managementTokenServer());
        $client->request('POST', '/management/discovery/rebuild', [], [], $this->managementTokenServer());

        $exportClient = $this->createDiscoveryClient($this->managementTokenServer());
        $exportClient->request('GET', '/management/discovery/rollback/export', [], [], $this->managementTokenServer());
        $planPayload = json_decode((string) $exportClient->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $executeClient = $this->createDiscoveryClient($this->managementTokenServer());
        $executeClient->request('POST', '/management/discovery/rollback/execute', [
            'current' => $planPayload['data']['currentEvidenceId'],
            'target' => $planPayload['data']['previousEvidenceId'],
        ], [], $this->managementTokenServer());

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $executeClient->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertTrue($payload['ok']);
        self::assertSame('discovery.rollback.execution', $payload['meta']['schemaFamily']);
        self::assertTrue($payload['data']['executed']);
        self::assertSame('rollback_executed', $payload['data']['status']);
        self::assertSame('global', $payload['data']['alias']);
        self::assertNotEmpty($payload['data']['targetPhysicalIndex']);
    }

    public function testManagementOverviewPageRenders(): void
    {
        $client = $this->createDiscoveryClient($this->managementTokenServer());
        $client->request('GET', '/management/discovery', [], [], $this->managementTokenServer());

        self::assertResponseIsSuccessful();

        $content = (string) $client->getResponse()->getContent();

        self::assertStringContainsString('Discovery Management', $content);
        self::assertStringContainsString('Recent operations', $content);
        self::assertStringContainsString('State topology', $content);
        self::assertStringContainsString('Platform diagnostics', $content);
        self::assertStringContainsString('Run backend probes', $content);
        self::assertStringContainsString('Rollback posture', $content);
        self::assertStringContainsString('Recommended command', $content);
        self::assertStringContainsString('Execute rollback', $content);
        self::assertStringContainsString('Distributed ready', $content);
        self::assertStringContainsString('Multi-replica-ready stores', $content);
        self::assertStringContainsString('Coverage by resource type', $content);
        self::assertStringContainsString('Coverage by source', $content);
    }
}
