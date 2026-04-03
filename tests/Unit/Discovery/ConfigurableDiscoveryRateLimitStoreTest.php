<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\RateLimit\ConfigurableDiscoveryRateLimitStore;
use App\Service\Discovery\RateLimit\FileDiscoveryRateLimitStore;
use App\Service\Discovery\RateLimit\PdoDiscoveryRateLimitStore;
use PHPUnit\Framework\TestCase;

final class ConfigurableDiscoveryRateLimitStoreTest extends TestCase
{
    public function testSelectsFileBackendWhenConfigured(): void
    {
        $path = sys_get_temp_dir() . '/discovering-rate-limit-configurable-' . bin2hex(random_bytes(6)) . '.json';
        $store = new ConfigurableDiscoveryRateLimitStore(
            new FileDiscoveryRateLimitStore($path),
            new PdoDiscoveryRateLimitStore('', null, null, 'discovery_rate_limit_bucket'),
            backend: 'file',
        );

        $bucket = $store->increment('query', 'ip:127.0.0.1', 60);

        self::assertSame(1, $bucket['count']);
        self::assertFileExists($path);
        unlink($path);
    }

    public function testRejectsUnknownBackend(): void
    {
        $path = sys_get_temp_dir() . '/discovering-rate-limit-configurable-' . bin2hex(random_bytes(6)) . '.json';
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
