<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\RateLimit\DiscoveryRateLimiter;
use App\Service\Discovery\RateLimit\FileDiscoveryRateLimitStore;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class DiscoveryRateLimiterTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = sys_get_temp_dir() . '/discovering-rate-limit-' . bin2hex(random_bytes(6)) . '.json';
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }
    }

    public function testQueryScopeExceedsConfiguredLimit(): void
    {
        $limiter = new DiscoveryRateLimiter(
            new FileDiscoveryRateLimitStore($this->path),
            queryLimit: 2,
            queryWindowSeconds: 60,
            writeLimit: 5,
            writeWindowSeconds: 60,
            managementMutationLimit: 5,
            managementMutationWindowSeconds: 60,
        );

        $request = Request::create('/api/v1/discovery', 'GET');
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

    public function testManagementMutationScopeUsesDedicatedBucket(): void
    {
        $limiter = new DiscoveryRateLimiter(
            new FileDiscoveryRateLimitStore($this->path),
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
