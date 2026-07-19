<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Service\Discovery\Diagnostics\DiscoveryBackendReachabilityBuilder;
use App\Discovering\ServiceInterface\Discovery\Diagnostics\DiscoveryProbeTransportInterface;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the discovery backend reachability builder test case for the Discovering component.
 */
final class DiscoveryBackendReachabilityBuilderTest extends TestCase
{
    public function testBuildMarksLocalOnlyModeAsNotConfigured(): void
    {
        $builder = new DiscoveryBackendReachabilityBuilder(
            transport: new class implements DiscoveryProbeTransportInterface {
                public function probeHttp(string $baseUrl, ?string $apiKey = null): array
                {
                    return ['reachable' => true, 'details' => ['HTTP 200']];
                }
            },
            indexBackend: 'sqlite',
            meiliUrl: '',
            meiliApiKey: '',
            feedbackBackend: 'file',
            operationLogBackend: 'file',
            rebuildEvidenceBackend: 'file',
            libsourceEventLogBackend: 'file',
            rateLimitBackend: 'file',
        );

        $report = $builder->build();

        self::assertSame(0, $report->performedProbeCount);
        self::assertSame(0, $report->reachableProbeCount);
        self::assertSame(0, $report->failingProbeCount);
        self::assertSame(6, $report->skippedProbeCount);
        self::assertSame('not_configured', $report->overallStatus);
        self::assertSame([], $report->failingProbeNames);
        self::assertStringContainsString('No shared backends are configured yet', $report->recommendedAction);
        self::assertContains('No shared backend probes were performed because every configured store remains in local-only mode.', $report->notes);
    }

    public function testBuildProbesConfiguredSharedBackends(): void
    {
        $builder = new DiscoveryBackendReachabilityBuilder(
            transport: new class implements DiscoveryProbeTransportInterface {
                public function probeHttp(string $baseUrl, ?string $apiKey = null): array
                {
                    return ['reachable' => true, 'details' => ['HTTP 200']];
                }
            },
            indexBackend: 'meili',
            meiliUrl: 'http://meili.internal:7700',
            meiliApiKey: 'secret',
            feedbackBackend: 'doctrine',
            operationLogBackend: 'doctrine',
            rebuildEvidenceBackend: 'file',
            libsourceEventLogBackend: 'doctrine',
            rateLimitBackend: 'doctrine',
        );

        $report = $builder->build();

        self::assertSame(5, $report->performedProbeCount);
        self::assertSame(5, $report->reachableProbeCount);
        self::assertSame(0, $report->failingProbeCount);
        self::assertSame(1, $report->skippedProbeCount);
        self::assertSame('healthy', $report->overallStatus);
        self::assertSame('reachable', $report->probes[1]->status);
        self::assertSame('reachable', $report->probes[2]->status);
        self::assertSame([], $report->failingProbeNames);
        self::assertContains('All performed backend reachability probes completed successfully.', $report->notes);
    }
}
