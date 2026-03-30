<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoveryQuery;
use PHPUnit\Framework\TestCase;

final class DiscoveryQueryTest extends TestCase
{
    public function testDefaultLimitIsPositive(): void
    {
        self::assertGreaterThan(0, new DiscoveryQuery()->limit);
    }
}
