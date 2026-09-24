<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Service\RateLimit\DiscoveryFileRateLimitStore;
use App\Discovering\Service\RateLimit\DiscoveryRateLimiter;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Exercises the discovery rate limiter test case for the Discovering component.
 */
final class DiscoveryRateLimiterTest extends DiscoveryTempFilesystemTestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = $this->createTempJsonPath('discovering-rate-limit-');
    }

    public function testQueryScopeExceedsConfiguredLimit(): void
    {
        $limiter = new DiscoveryRateLimiter(
            new DiscoveryFileRateLimitStore($this->path),
            queryLimit: 2,
            queryWindowSeconds: 60,
            writeLimit: 5,
            writeWindowSeconds: 60,
            managementMutationLimit: 5,
            managementMutationWindowSeconds: 60,
        );

        $request = Request::create('/api/discovery', 'GET');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $first = $limiter->consumeForRequest($request);
        $second = $limiter->consumeForRequest($request);
        $third = $limiter->consumeForRequest($request);

        self::assertNotNull($first);
        self::assertSame('query', $first->scope);
        self::assertFalse($first->exceeded);
        self::assertSame(1, $first->remaining);
        self::assertFalse($second?->exceeded);
        self::assertTrue($third?->exceeded);
        self::assertSame(0, $third?->remaining);
        self::assertGreaterThan(0, $third?->retryAfterSeconds ?? 0);
    }

    public function testCorruptedRateLimitStateFailsObservably(): void
    {
        file_put_contents($this->path, '{invalid-json');

        $store = new DiscoveryFileRateLimitStore($this->path);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('contains invalid JSON');
        $store->increment('query', 'ip:127.0.0.3', 60);
    }

    public function testManagementMutationScopeUsesDedicatedBucket(): void
    {
        $limiter = new DiscoveryRateLimiter(
            new DiscoveryFileRateLimitStore($this->path),
            queryLimit: 5,
            queryWindowSeconds: 60,
            writeLimit: 5,
            writeWindowSeconds: 60,
            managementMutationLimit: 1,
            managementMutationWindowSeconds: 60,
        );

        $request = Request::create('/management/discovery/briefing', 'GET', ['action' => 'audit-registry']);
        $request->server->set('REMOTE_ADDR', '127.0.0.2');
        $request->headers->set('X-Discovery-Management-Token', 'test-token');

        $first = $limiter->consumeForRequest($request);
        $second = $limiter->consumeForRequest($request);

        self::assertSame('management_mutation', $first?->scope);
        self::assertFalse($first?->exceeded);
        self::assertTrue($second?->exceeded);
    }
}
