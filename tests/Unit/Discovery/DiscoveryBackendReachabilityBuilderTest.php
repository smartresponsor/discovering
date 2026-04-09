<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Diagnostics\DiscoveryBackendReachabilityBuilder;
use App\Service\Discovery\Diagnostics\DiscoveryProbeTransportInterface;
use PHPUnit\Framework\TestCase;


/**
 * Exercises the discovery backend reachability builder test case for the Discovering component.
 */
final class DiscoveryBackendReachabilityBuilderTest extends TestCase
{
    public function testBuildMarksLocalOnlyModeAsNotConfigured(): void
    {
        $builder = new DiscoveryBackendReachabilityBuilder(
            transport: new class() implements DiscoveryProbeTransportInterface {
                public function probeHttp(string $baseUrl, ?string $apiKey = null): array
                {
                    return ['reachable' => true, 'details' => ['HTTP 200']];
                }

                public function probePdo(string $dsn, ?string $user = null, ?string $password = null): array
                {
                    return ['reachable' => true, 'details' => ['SELECT 1']];
                }
            },
            indexBackend: 'sqlite',
            meiliUrl: '',
            meiliApiKey: '',
            feedbackBackend: 'sqlite_path',
            feedbackPdoDsn: '',
            feedbackPdoUser: null,
            feedbackPdoPassword: null,
            operationLogBackend: 'file',
            operationLogPdoDsn: '',
            operationLogPdoUser: null,
            operationLogPdoPassword: null,
            rebuildEvidenceBackend: 'file',
            rebuildEvidencePdoDsn: '',
            rebuildEvidencePdoUser: null,
            rebuildEvidencePdoPassword: null,
            libsourceEventLogBackend: 'file',
            libsourceEventLogPdoDsn: '',
            libsourceEventLogPdoUser: null,
            libsourceEventLogPdoPassword: null,
            rateLimitBackend: 'file',
            rateLimitPdoDsn: '',
            rateLimitPdoUser: null,
            rateLimitPdoPassword: null,
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
            transport: new class() implements DiscoveryProbeTransportInterface {
                public function probeHttp(string $baseUrl, ?string $apiKey = null): array
                {
                    return ['reachable' => true, 'details' => ['HTTP 200']];
                }

                public function probePdo(string $dsn, ?string $user = null, ?string $password = null): array
                {
                    return ['reachable' => !str_contains($dsn, 'fail'), 'details' => [str_contains($dsn, 'fail') ? 'connection refused' : 'SELECT 1']];
                }
            },
            indexBackend: 'meili',
            meiliUrl: 'http://meili.internal:7700',
            meiliApiKey: 'secret',
            feedbackBackend: 'pdo',
            feedbackPdoDsn: 'pgsql:host=db.internal;dbname=discovering',
            feedbackPdoUser: 'user',
            feedbackPdoPassword: 'pass',
            operationLogBackend: 'pdo',
            operationLogPdoDsn: 'pgsql:host=fail.internal;dbname=discovering',
            operationLogPdoUser: 'user',
            operationLogPdoPassword: 'pass',
            rebuildEvidenceBackend: 'file',
            rebuildEvidencePdoDsn: '',
            rebuildEvidencePdoUser: null,
            rebuildEvidencePdoPassword: null,
            libsourceEventLogBackend: 'pdo',
            libsourceEventLogPdoDsn: 'pgsql:host=db.internal;dbname=discovering',
            libsourceEventLogPdoUser: 'user',
            libsourceEventLogPdoPassword: 'pass',
            rateLimitBackend: 'pdo',
            rateLimitPdoDsn: 'pgsql:host=db.internal;dbname=discovering',
            rateLimitPdoUser: 'user',
            rateLimitPdoPassword: 'pass',
        );

        $report = $builder->build();

        self::assertSame(5, $report->performedProbeCount);
        self::assertSame(4, $report->reachableProbeCount);
        self::assertSame(1, $report->failingProbeCount);
        self::assertSame(1, $report->skippedProbeCount);
        self::assertSame('degraded', $report->overallStatus);
        self::assertSame('reachable', $report->probes[0]->status);
        self::assertSame('unreachable', $report->probes[2]->status);
        self::assertSame(['operationLog'], $report->failingProbeNames);
        self::assertStringContainsString('operationLog', $report->recommendedAction);
        self::assertContains('1 backend reachability probe(s) failed.', $report->notes);
    }
}
