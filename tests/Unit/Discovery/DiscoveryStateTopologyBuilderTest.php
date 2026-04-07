<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Topology\DiscoveryStateTopologyBuilder;
use PHPUnit\Framework\TestCase;


/**
 * Exercises the discovery state topology builder test case for the Discovering component.
 */
final class DiscoveryStateTopologyBuilderTest extends TestCase
{
    public function testBuildMarksLocalVarDiscoveryPathsAsLocalAndNotDistributedReady(): void
    {
        $builder = $this->builder();

        $topology = $builder->build();

        self::assertFalse($topology->sharedStateConfigured);
        self::assertFalse($topology->distributedReady);
        self::assertSame('/workspace/discovering/var/discovery', $topology->localStateRoot);
        self::assertCount(6, $topology->stores);
        self::assertSame('sqlite', $topology->stores[0]->backend);
        self::assertSame('local_file', $topology->stores[0]->storageMode);
        self::assertFalse($topology->stores[0]->sharedConfigured);
        self::assertContains('All discovery state paths still resolve under the local var/discovery root.', $topology->notes);
    }

    public function testBuildRecognizesExternalizedPathsButKeepsDistributedReadinessFalse(): void
    {
        $builder = $this->builder(
            discoverySqlitePath: '/mnt/shared/discovering/discovering.sqlite',
            feedbackPath: '/mnt/shared/discovering/discovering-feedback.sqlite',
            operationLogPath: '/mnt/shared/discovering/discovery-operation-log.json',
            rebuildEvidencePath: '/mnt/shared/discovering/discovery-rebuild-evidence.json',
            libsourceEventLogPath: '/mnt/shared/discovering/libsource-operator-event-log.json',
            rateLimitStorePath: '/mnt/shared/discovering/discovery-rate-limit.json',
        );

        $topology = $builder->build();

        self::assertTrue($topology->sharedStateConfigured);
        self::assertFalse($topology->distributedReady);
        self::assertSame('shared_file', $topology->stores[0]->storageMode);
        self::assertTrue($topology->stores[0]->sharedConfigured);
        self::assertContains('shared-filesystem-locking-risk', $topology->stores[0]->concerns);
        self::assertContains('shared-filesystem-append-risk', $topology->stores[2]->concerns);
    }

    public function testBuildMarksPdoCoordinationStoresAsSharedButStillNotOverallDistributedReady(): void
    {
        $builder = $this->builder(
            operationLogBackend: 'pdo',
            operationLogPdoDsn: 'pgsql:host=db.internal;dbname=discovering',
            rebuildEvidenceBackend: 'pdo',
            rebuildEvidencePdoDsn: 'pgsql:host=db.internal;dbname=discovering',
            libsourceEventLogBackend: 'pdo',
            libsourceEventLogPdoDsn: 'pgsql:host=db.internal;dbname=discovering',
            rateLimitBackend: 'pdo',
            rateLimitPdoDsn: 'pgsql:host=db.internal;dbname=discovering',
        );

        $topology = $builder->build();

        self::assertTrue($topology->sharedStateConfigured);
        self::assertFalse($topology->distributedReady);
        self::assertSame('pdo_table', $topology->stores[2]->backend);
        self::assertSame('pdo_table', $topology->stores[3]->backend);
        self::assertSame('pdo_table', $topology->stores[4]->backend);
        self::assertSame('pdo_table', $topology->stores[5]->backend);
        self::assertTrue($topology->stores[2]->multiReplicaWriteReady);
        self::assertTrue($topology->stores[5]->multiReplicaWriteReady);
        self::assertContains('Operation log uses a shared PDO coordination backend.', $topology->notes);
        self::assertContains('Rebuild evidence uses a shared PDO coordination backend.', $topology->notes);
        self::assertContains('Libsource event log uses a shared PDO coordination backend.', $topology->notes);
    }

    public function testBuildRecognizesSharedPdoFeedbackButStillKeepsDistributedReadinessFalseUntilIndexMoves(): void
    {
        $builder = $this->builder(
            feedbackBackend: 'pdo',
            feedbackPdoDsn: 'pgsql:host=db.internal;dbname=discovering',
        );

        $topology = $builder->build();

        self::assertTrue($topology->sharedStateConfigured);
        self::assertFalse($topology->distributedReady);
        self::assertSame('pdo_table', $topology->stores[1]->backend);
        self::assertTrue($topology->stores[1]->multiReplicaWriteReady);
        self::assertContains('Feedback learning uses a shared PDO coordination backend.', $topology->notes);
        self::assertContains('Overall distributed readiness still remains false until discovery index moves beyond SQLite single-node storage.', $topology->notes);
    }


    public function testBuildCanTreatTopologyAsDistributedReadyWhenIndexAndMutableStateUseSharedBackends(): void
    {
        $builder = $this->builder(
            indexBackend: 'meili',
            meiliUrl: 'http://meili.internal:7700',
            meiliIndexPrefix: 'discovering_prod',
            feedbackBackend: 'pdo',
            feedbackPdoDsn: 'pgsql:host=db.internal;dbname=discovering',
            operationLogBackend: 'pdo',
            operationLogPdoDsn: 'pgsql:host=db.internal;dbname=discovering',
            rebuildEvidenceBackend: 'pdo',
            rebuildEvidencePdoDsn: 'pgsql:host=db.internal;dbname=discovering',
            libsourceEventLogBackend: 'pdo',
            libsourceEventLogPdoDsn: 'pgsql:host=db.internal;dbname=discovering',
            rateLimitBackend: 'pdo',
            rateLimitPdoDsn: 'pgsql:host=db.internal;dbname=discovering',
        );

        $topology = $builder->build();

        self::assertTrue($topology->sharedStateConfigured);
        self::assertTrue($topology->distributedReady);
        self::assertSame('meilisearch', $topology->stores[0]->backend);
        self::assertSame('service', $topology->stores[0]->storageMode);
        self::assertTrue($topology->stores[0]->multiReplicaWriteReady);
        self::assertContains('Discovery index uses a shared Meilisearch backend.', $topology->notes);
        self::assertContains('Overall distributed readiness can be treated as true when stronger coordination stores are configured for the remaining mutable discovery state.', $topology->notes);
    }

    private function builder(
        string $discoverySqlitePath = '/workspace/discovering/var/discovery/discovering.sqlite',
        string $indexBackend = 'sqlite',
        string $meiliUrl = '',
        string $meiliIndexPrefix = 'discovering',
        string $feedbackPath = '/workspace/discovering/var/discovery/discovering-feedback.sqlite',
        string $feedbackBackend = 'sqlite_path',
        string $feedbackPdoDsn = '',
        string $feedbackPdoTable = 'discovery_feedback',
        string $operationLogPath = '/workspace/discovering/var/discovery/discovery-operation-log.json',
        string $operationLogBackend = 'file',
        string $operationLogPdoDsn = '',
        string $operationLogPdoTable = 'discovery_operation_event_log',
        string $rebuildEvidencePath = '/workspace/discovering/var/discovery/discovery-rebuild-evidence.json',
        string $rebuildEvidenceBackend = 'file',
        string $rebuildEvidencePdoDsn = '',
        string $rebuildEvidencePdoTable = 'discovery_rebuild_evidence',
        string $libsourceEventLogPath = '/workspace/discovering/var/discovery/libsource-operator-event-log.json',
        string $libsourceEventLogBackend = 'file',
        string $libsourceEventLogPdoDsn = '',
        string $libsourceEventLogPdoTable = 'discovery_libsource_operator_event_log',
        string $rateLimitBackend = 'file',
        string $rateLimitStorePath = '/workspace/discovering/var/discovery/discovery-rate-limit.json',
        string $rateLimitPdoDsn = '',
        string $rateLimitPdoTable = 'discovery_rate_limit_bucket',
    ): DiscoveryStateTopologyBuilder {
        return new DiscoveryStateTopologyBuilder(
            projectDir: '/workspace/discovering',
            discoverySqlitePath: $discoverySqlitePath,
            indexBackend: $indexBackend,
            meiliUrl: $meiliUrl,
            meiliIndexPrefix: $meiliIndexPrefix,
            feedbackPath: $feedbackPath,
            feedbackBackend: $feedbackBackend,
            feedbackPdoDsn: $feedbackPdoDsn,
            feedbackPdoTable: $feedbackPdoTable,
            operationLogPath: $operationLogPath,
            operationLogBackend: $operationLogBackend,
            operationLogPdoDsn: $operationLogPdoDsn,
            operationLogPdoTable: $operationLogPdoTable,
            rebuildEvidencePath: $rebuildEvidencePath,
            rebuildEvidenceBackend: $rebuildEvidenceBackend,
            rebuildEvidencePdoDsn: $rebuildEvidencePdoDsn,
            rebuildEvidencePdoTable: $rebuildEvidencePdoTable,
            libsourceEventLogPath: $libsourceEventLogPath,
            libsourceEventLogBackend: $libsourceEventLogBackend,
            libsourceEventLogPdoDsn: $libsourceEventLogPdoDsn,
            libsourceEventLogPdoTable: $libsourceEventLogPdoTable,
            rateLimitBackend: $rateLimitBackend,
            rateLimitStorePath: $rateLimitStorePath,
            rateLimitPdoDsn: $rateLimitPdoDsn,
            rateLimitPdoTable: $rateLimitPdoTable,
        );
    }
}
