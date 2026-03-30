<?php
declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoveryQuery;
use PHPUnit\Framework\TestCase;

final class DiscoveryQueryTest extends TestCase
{
    public function testFromArrayAppliesDefaults(): void
    {
        $query = DiscoveryQuery::fromArray(['query' => 'alpha']);
        self::assertSame('alpha', $query->query);
        self::assertSame('global', $query->resource);
        self::assertSame(20, $query->limit);
        self::assertSame(0, $query->offset);
    }
}
