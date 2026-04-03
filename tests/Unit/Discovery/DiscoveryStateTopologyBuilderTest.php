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
            rateLimitStorePath: '/workspace/discovering/var/discovery/discovery-rate-limit.json',
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
}
