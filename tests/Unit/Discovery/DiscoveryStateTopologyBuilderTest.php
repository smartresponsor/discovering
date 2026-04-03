<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Topology\DiscoveryStateTopologyBuilder;
use PHPUnit\Framework\TestCase;

final class DiscoveryStateTopologyBuilderTest extends TestCase
{
    public function testBuildMarksLocalVarDiscoveryPathsAsLocalAndNotDistributedReady(): void
    {
        $builder = new DiscoveryStateTopologyBuilder(
            projectDir: '/workspace/discovering',
            discoverySqlitePath: '/workspace/discovering/var/discovery/discovering.sqlite',
            feedbackSqlitePath: '/workspace/discovering/var/discovery/discovering-feedback.sqlite',
            operationLogPath: '/workspace/discovering/var/discovery/discovery-operation-log.json',
            rebuildEvidencePath: '/workspace/discovering/var/discovery/discovery-rebuild-evidence.json',
            libsourceEventLogPath: '/workspace/discovering/var/discovery/libsource-operator-event-log.json',
            rateLimitBackend: 'file',
            rateLimitStorePath: '/workspace/discovering/var/discovery/discovery-rate-limit.json',
            rateLimitPdoDsn: '',
            rateLimitPdoTable: 'discovery_rate_limit_bucket',
        );

        $topology = $builder->build();

        self::assertFalse($topology->sharedStateConfigured);
        self::assertFalse($topology->distributedReady);
        self::assertSame('/workspace/discovering/var/discovery', $topology->localStateRoot);
        self::assertCount(6, $topology->stores);
        self::assertSame('local_file', $topology->stores[0]->storageMode);
        self::assertFalse($topology->stores[0]->sharedConfigured);
        self::assertContains('All discovery state paths still resolve under the local var/discovery root.', $topology->notes);
    }

    public function testBuildRecognizesExternalizedPathsButKeepsDistributedReadinessFalse(): void
    {
        $builder = new DiscoveryStateTopologyBuilder(
            projectDir: '/workspace/discovering',
            discoverySqlitePath: '/mnt/shared/discovering/discovering.sqlite',
            feedbackSqlitePath: '/mnt/shared/discovering/discovering-feedback.sqlite',
            operationLogPath: '/mnt/shared/discovering/discovery-operation-log.json',
            rebuildEvidencePath: '/mnt/shared/discovering/discovery-rebuild-evidence.json',
            libsourceEventLogPath: '/mnt/shared/discovering/libsource-operator-event-log.json',
            rateLimitBackend: 'file',
            rateLimitStorePath: '/mnt/shared/discovering/discovery-rate-limit.json',
            rateLimitPdoDsn: '',
            rateLimitPdoTable: 'discovery_rate_limit_bucket',
        );

        $topology = $builder->build();

        self::assertTrue($topology->sharedStateConfigured);
        self::assertFalse($topology->distributedReady);
        self::assertSame('shared_file', $topology->stores[0]->storageMode);
        self::assertTrue($topology->stores[0]->sharedConfigured);
        self::assertContains('shared-filesystem-locking-risk', $topology->stores[0]->concerns);
        self::assertContains('shared-filesystem-append-risk', $topology->stores[2]->concerns);
    }

    public function testBuildMarksPdoRateLimitStoreAsSharedCoordinationBackend(): void
    {
        $builder = new DiscoveryStateTopologyBuilder(
            projectDir: '/workspace/discovering',
            discoverySqlitePath: '/workspace/discovering/var/discovery/discovering.sqlite',
            feedbackSqlitePath: '/workspace/discovering/var/discovery/discovering-feedback.sqlite',
            operationLogPath: '/workspace/discovering/var/discovery/discovery-operation-log.json',
            rebuildEvidencePath: '/workspace/discovering/var/discovery/discovery-rebuild-evidence.json',
            libsourceEventLogPath: '/workspace/discovering/var/discovery/libsource-operator-event-log.json',
            rateLimitBackend: 'pdo',
            rateLimitStorePath: '/workspace/discovering/var/discovery/discovery-rate-limit.json',
            rateLimitPdoDsn: 'pgsql:host=db.internal;dbname=discovering',
            rateLimitPdoTable: 'discovery_rate_limit_bucket',
        );

        $topology = $builder->build();
        $rateLimitStore = $topology->stores[5];

        self::assertTrue($topology->sharedStateConfigured);
        self::assertFalse($topology->distributedReady);
        self::assertSame('pdo_table', $rateLimitStore->backend);
        self::assertSame('database', $rateLimitStore->storageMode);
        self::assertTrue($rateLimitStore->sharedConfigured);
        self::assertTrue($rateLimitStore->multiReplicaWriteReady);
        self::assertContains('shared-counter-coordination', $rateLimitStore->concerns);
        self::assertContains('Rate limiting can now use a shared PDO coordination backend, but overall distributed readiness still remains false until other mutable discovery stores move beyond SQLite and JSON files.', $topology->notes);
    }
}
