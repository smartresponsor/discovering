<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\RateLimit\PdoDiscoveryRateLimitStore;
use PHPUnit\Framework\TestCase;


/**
 * Exercises the pdo discovery rate limit store test case for the Discovering component.
 */
final class PdoDiscoveryRateLimitStoreTest extends TestCase
{
    public function testRejectsMissingDsnBeforeAttemptingCoordination(): void
    {
        $store = new PdoDiscoveryRateLimitStore('', null, null, 'discovery_rate_limit_bucket');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('APP_DISCOVERY_RATE_LIMIT_PDO_DSN');
        $store->increment('query', 'ip:127.0.0.1', 60);
    }
}
