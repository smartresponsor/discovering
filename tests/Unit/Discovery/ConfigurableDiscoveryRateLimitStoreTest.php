<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\RateLimit\ConfigurableDiscoveryRateLimitStore;
use App\Service\Discovery\RateLimit\FileDiscoveryRateLimitStore;
use App\Service\Discovery\RateLimit\PdoDiscoveryRateLimitStore;
use App\Tests\Support\DiscoveryTempFilesystemTestCase;


/**
 * Exercises the configurable discovery rate limit store test case for the Discovering component.
 */
final class ConfigurableDiscoveryRateLimitStoreTest extends DiscoveryTempFilesystemTestCase
{
    public function testSelectsFileBackendWhenConfigured(): void
    {
        $path = $this->createTempJsonPath('discovering-rate-limit-configurable-');
        $store = new ConfigurableDiscoveryRateLimitStore(
            new FileDiscoveryRateLimitStore($path),
            new PdoDiscoveryRateLimitStore('', null, null, 'discovery_rate_limit_bucket'),
            backend: 'file',
        );

        $bucket = $store->increment('query', 'ip:127.0.0.1', 60);

        self::assertSame(1, $bucket['count']);
        self::assertFileExists($path);
    }

    public function testRejectsUnknownBackend(): void
    {
        $path = $this->createTempJsonPath('discovering-rate-limit-configurable-');
        $store = new ConfigurableDiscoveryRateLimitStore(
            new FileDiscoveryRateLimitStore($path),
            new PdoDiscoveryRateLimitStore('', null, null, 'discovery_rate_limit_bucket'),
            backend: 'redis',
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unsupported discovery rate limit backend');
        $store->increment('query', 'ip:127.0.0.1', 60);
    }
}
