<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Diagnostics\DiscoveryBackendReachabilityBuilder;
use App\Service\Discovery\Diagnostics\DiscoveryProbeTransportInterface;
use PHPUnit\Framework\TestCase;

final class DiscoveryBackendReachabilityBuilderTest extends TestCase
{
    public function testBuildSkipsLocalOnlyStoresInSingleNodeMode(): void
    {
        $builder = new DiscoveryBackendReachabilityBuilder(
            transport: new class() implements DiscoveryProbeTransportInterface {
                public function probeHttp(string $baseUrl, ?string $apiKey = null): array { return ['reachable' => false, 'details' => ['unexpected']]; }
                public function probePdo(string $dsn, ?string $user = null, ?string $password = null): array { return ['reachable' => false, 'details' => ['unexpected']]; }
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
        self::assertSame('local_only', $report->probes[0]->status);
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
        self::assertSame('reachable', $report->probes[0]->status);
        self::assertSame('unreachable', $report->probes[2]->status);
        self::assertContains('1 backend reachability probe(s) failed.', $report->notes);
    }
}
