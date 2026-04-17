<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Http\DiscoveryRequestSurfacePolicy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Exercises the discovery request surface policy test case for the Discovering component.
 */
final class DiscoveryRequestSurfacePolicyTest extends TestCase
{
    public function testResolveScopePrioritizesWriteBeforeManagementAndQuery(): void
    {
        $policy = new DiscoveryRequestSurfacePolicy();

        $writeRequest = Request::create('/api/v1/discovery/click', 'POST');
        $writeRequest->server->set('REMOTE_ADDR', '127.0.0.1');

        self::assertSame('write', $policy->resolveScope($writeRequest));

        $managementRequest = Request::create('/management/discovery/rebuild', 'GET', ['action' => 'audit-registry']);
        $managementRequest->server->set('REMOTE_ADDR', '127.0.0.1');

        self::assertSame('management_mutation', $policy->resolveScope($managementRequest));

        $queryRequest = Request::create('/api/v1/discovery', 'GET');
        $queryRequest->server->set('REMOTE_ADDR', '127.0.0.1');

        self::assertSame('query', $policy->resolveScope($queryRequest));
    }

    public function testWantsJsonResponseDetectsApiAndExportPaths(): void
    {
        $policy = new DiscoveryRequestSurfacePolicy();

        self::assertTrue($policy->wantsJsonResponse('/api/v1/discovery'));
        self::assertTrue($policy->wantsJsonResponse('/management/discovery/rebuild'));
        self::assertTrue($policy->wantsJsonResponse('/management/discovery/briefing/export'));
        self::assertTrue($policy->wantsJsonResponse('/management/discovery/inspect/briefing'));
        self::assertFalse($policy->wantsJsonResponse('/discovery'));
    }
}
