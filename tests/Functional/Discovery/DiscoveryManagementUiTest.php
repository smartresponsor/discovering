<?php

declare(strict_types=1);

namespace App\Tests\Functional\Discovery;



/**
 * Exercises the discovery management ui test case for the Discovering component.
 */
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
        $payload = $this->managementExportPayload('/management/discovery/export');
        self::assertSame('sqlite-fts5', $payload['data']['backendName']);
        self::assertGreaterThanOrEqual(1, $payload['data']['totalDocuments']);
        self::assertArrayHasKey('briefing', $payload['data']['countsByResourceType']);
        self::assertArrayHasKey('briefing-file-source-provider', $payload['data']['countsBySourceName']);
        self::assertNotEmpty($payload['data']['sampleDocuments']);
    }

    public function testManagementOperationsExportReturnsRecordedEvents(): void
    {
        $client = $this->createDiscoveryClient();
        $this->requestPublicDiscoveryQuery($client);

        $payload = $this->managementExportPayload('/management/discovery/operations/export');
        self::assertNotEmpty($payload['data']);
        self::assertContains('discovery.api.query', array_column($payload['data'], 'operation'));
    }

    public function testManagementRebuildReturnsEvidenceSummary(): void
    {
        $payload = $this->managementMutationPayload('/management/discovery/rebuild', schemaFamily: 'discovery.rebuild.summary');
        self::assertSame('global', $payload['data']['resource']);
        self::assertSame('staged_alias_swap', $payload['data']['deploymentMode']);
        self::assertTrue($payload['data']['zeroDowntimeReady']);
        self::assertTrue($payload['data']['aliasSwapApplied']);
        self::assertArrayHasKey('global', $payload['data']['stagedIndexes']);
        self::assertStringStartsWith('reb-', $payload['data']['evidenceId']);
    }

    public function testManagementRebuildExportReturnsRecordedEvidence(): void
    {
        $this->performManagementRebuilds(1);

        $payload = $this->managementExportPayload('/management/discovery/rebuilds/export', 'discovery.rebuild.summary.list');
        self::assertNotEmpty($payload['data']);
        self::assertSame('staged_alias_swap', $payload['data'][0]['deploymentMode']);
        self::assertTrue($payload['data'][0]['aliasSwapApplied']);
        self::assertStringStartsWith('reb-', $payload['data'][0]['evidenceId']);
    }

    public function testManagementStateTopologyExportReturnsDistributedReadinessPosture(): void
    {
        $payload = $this->managementExportPayload('/management/discovery/state-topology/export', 'discovery.state.topology');
        self::assertFalse($payload['data']['distributedReady']);
        self::assertSame('local_file', $payload['data']['stores'][0]['storageMode']);
        self::assertArrayHasKey('notes', $payload['data']);
    }

    public function testManagementPlatformProbesExportReturnsReachabilitySummary(): void
    {
        $payload = $this->managementExportPayload('/management/discovery/platform/probes/export', 'discovery.platform.probes');
        self::assertSame(0, $payload['data']['performedProbeCount']);
        self::assertSame(6, $payload['data']['skippedProbeCount']);
        self::assertSame('not_configured', $payload['data']['overallStatus']);
        self::assertCount(6, $payload['data']['probes']);
        self::assertSame('local_only', $payload['data']['probes'][0]['status']);
    }

    public function testManagementPlatformExportReturnsPlatformDiagnostics(): void
    {
        $payload = $this->managementExportPayload('/management/discovery/platform/export', 'discovery.platform.diagnostics');
        self::assertSame('sqlite-fts5', $payload['data']['backendName']);
        self::assertSame('sqlite', $payload['data']['indexStoreBackend']);
        self::assertTrue($payload['data']['stagedRebuildSupported']);
        self::assertFalse($payload['data']['distributedReady']);
        self::assertSame('local_only', $payload['data']['postureStatus']);
        self::assertSame('medium', $payload['data']['riskLevel']);
        self::assertArrayHasKey('recommendedAction', $payload['data']);
        self::assertArrayHasKey('blockingStores', $payload['data']);
        self::assertArrayHasKey('notes', $payload['data']);
    }


    public function testManagementRollbackExportReturnsRollbackPlan(): void
    {
        $this->performManagementRebuilds(2);

        $payload = $this->managementExportPayload('/management/discovery/rollback/export', 'discovery.rollback.plan');
        self::assertSame('plan_ready', $payload['data']['status']);
        self::assertTrue($payload['data']['rollbackReady']);
        self::assertNotEmpty($payload['data']['currentEvidenceId']);
        self::assertNotEmpty($payload['data']['previousEvidenceId']);
        self::assertNotEmpty($payload['data']['recommendedCommand']);
    }


    public function testManagementRollbackExecutePromotesRollbackTarget(): void
    {
        $planPayload = $this->prepareRollbackScenarioPayload();

        $payload = $this->executeRollbackPlanPayload($planPayload);
        self::assertTrue($payload['data']['executed']);
        self::assertSame('rollback_executed', $payload['data']['status']);
        self::assertSame('global', $payload['data']['alias']);
        self::assertNotEmpty($payload['data']['targetPhysicalIndex']);
    }

    public function testManagementOverviewPageRenders(): void
    {
        $content = $this->managementPageContent();

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
