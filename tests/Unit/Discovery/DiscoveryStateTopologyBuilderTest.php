<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Builder\Topology\DiscoveryStateTopologyBuilder;
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

        self::assertTrue($topology->sharedStateConfigured);
        self::assertFalse($topology->distributedReady);
        self::assertSame('/workspace/discovering/var/discovery', $topology->localStateRoot);
        self::assertCount(6, $topology->stores);
        self::assertSame('sqlite', $topology->stores[0]->backend);
        self::assertSame('local_file', $topology->stores[0]->storageMode);
        self::assertFalse($topology->stores[0]->sharedConfigured);
        self::assertSame('doctrine', $topology->stores[1]->backend);
        self::assertContains('One or more discovery state stores are configured outside the local var/discovery root or through a shared coordination backend.', $topology->notes);
    }

    public function testBuildRecognizesExternalizedPathsButKeepsDistributedReadinessFalse(): void
    {
        $builder = $this->builder(
            discoverySqlitePath: '/mnt/shared/discovering/discovering.sqlite',
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

    public function testBuildMarksDoctrineCoordinationStoresAsSharedButStillNotOverallDistributedReady(): void
    {
        $builder = $this->builder(
            operationLogBackend: 'doctrine',
            rebuildEvidenceBackend: 'doctrine',
            libsourceEventLogBackend: 'doctrine',
            rateLimitBackend: 'doctrine',
        );

        $topology = $builder->build();

        self::assertTrue($topology->sharedStateConfigured);
        self::assertFalse($topology->distributedReady);
        self::assertSame('doctrine', $topology->stores[2]->backend);
        self::assertSame('doctrine', $topology->stores[3]->backend);
        self::assertSame('doctrine', $topology->stores[4]->backend);
        self::assertSame('doctrine', $topology->stores[5]->backend);
        self::assertTrue($topology->stores[1]->multiReplicaWriteReady);
        self::assertTrue($topology->stores[5]->multiReplicaWriteReady);
        self::assertContains('Operation log uses a shared Doctrine ORM coordination backend.', $topology->notes);
        self::assertContains('Rebuild evidence uses a shared Doctrine ORM coordination backend.', $topology->notes);
        self::assertContains('Libsource event log uses a shared Doctrine ORM coordination backend.', $topology->notes);
        self::assertContains('Overall distributed readiness still remains false until every mutable discovery store moves beyond local file-backed state.', $topology->notes);
    }

    public function testBuildCanTreatTopologyAsDistributedReadyWhenIndexAndMutableStateUseSharedBackends(): void
    {
        $builder = $this->builder(
            indexBackend: 'meili',
            meiliUrl: 'http://meili.internal:7700',
            meiliIndexPrefix: 'discovering_prod',
            operationLogBackend: 'doctrine',
            rebuildEvidenceBackend: 'doctrine',
            libsourceEventLogBackend: 'doctrine',
            rateLimitBackend: 'doctrine',
        );

        $topology = $builder->build();

        self::assertTrue($topology->sharedStateConfigured);
        self::assertTrue($topology->distributedReady);
        self::assertSame('meilisearch', $topology->stores[0]->backend);
        self::assertSame('service', $topology->stores[0]->storageMode);
        self::assertTrue($topology->stores[0]->multiReplicaWriteReady);
        self::assertContains('Discovery index uses a shared Meilisearch backend.', $topology->notes);
        self::assertContains('Overall distributed readiness can be treated as true when the mutable discovery stores are backed by Doctrine or another shared backend.', $topology->notes);
    }

    private function builder(
        string $discoverySqlitePath = '/workspace/discovering/var/discovery/discovering.sqlite',
        string $indexBackend = 'sqlite',
        string $meiliUrl = '',
        string $meiliIndexPrefix = 'discovering',
        string $operationLogPath = '/workspace/discovering/var/discovery/discovery-operation-log.json',
        string $operationLogBackend = 'file',
        string $rebuildEvidencePath = '/workspace/discovering/var/discovery/discovery-rebuild-evidence.json',
        string $rebuildEvidenceBackend = 'file',
        string $libsourceEventLogPath = '/workspace/discovering/var/discovery/libsource-operator-event-log.json',
        string $libsourceEventLogBackend = 'file',
        string $rateLimitBackend = 'file',
        string $rateLimitStorePath = '/workspace/discovering/var/discovery/discovery-rate-limit.json',
    ): DiscoveryStateTopologyBuilder {
        return new DiscoveryStateTopologyBuilder(
            projectDir: '/workspace/discovering',
            discoverySqlitePath: $discoverySqlitePath,
            indexBackend: $indexBackend,
            meiliUrl: $meiliUrl,
            meiliIndexPrefix: $meiliIndexPrefix,
            operationLogPath: $operationLogPath,
            operationLogBackend: $operationLogBackend,
            rebuildEvidencePath: $rebuildEvidencePath,
            rebuildEvidenceBackend: $rebuildEvidenceBackend,
            libsourceEventLogPath: $libsourceEventLogPath,
            libsourceEventLogBackend: $libsourceEventLogBackend,
            rateLimitBackend: $rateLimitBackend,
            rateLimitStorePath: $rateLimitStorePath,
        );
    }
}
