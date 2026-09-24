<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Repository\RateLimit\DiscoveryDoctrineRateLimitStore;
use App\Discovering\Service\RateLimit\DiscoveryConfigurableRateLimitStore;
use App\Discovering\Service\RateLimit\DiscoveryFileRateLimitStore;
use App\Discovering\Tests\Support\DiscoveryDoctrineEntityManagerFactory;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;

/**
 * Exercises the configurable discovery rate limit store test case for the Discovering component.
 */
final class ConfigurableDiscoveryRateLimitStoreTest extends DiscoveryTempFilesystemTestCase
{
    public function testSelectsFileBackendWhenConfigured(): void
    {
        $path = $this->createTempJsonPath('discovering-rate-limit-configurable-');
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $store = new DiscoveryConfigurableRateLimitStore(
            new DiscoveryFileRateLimitStore($path),
            new DiscoveryDoctrineRateLimitStore($entityManager),
            backend: 'file',
        );

        $bucket = $store->increment('query', 'ip:127.0.0.1', 60);

        self::assertSame(1, $bucket['count']);
        self::assertFileExists($path);
    }

    public function testRejectsUnknownBackend(): void
    {
        $path = $this->createTempJsonPath('discovering-rate-limit-configurable-');
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $store = new DiscoveryConfigurableRateLimitStore(
            new DiscoveryFileRateLimitStore($path),
            new DiscoveryDoctrineRateLimitStore($entityManager),
            backend: 'redis',
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unsupported discovery rate limit backend');
        $store->increment('query', 'ip:127.0.0.1', 60);
    }
}
