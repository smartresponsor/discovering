<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Query\DiscoveryQueryFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class DiscoveryQueryFactoryTest extends TestCase
{
    public function testItBuildsTypedQueryFromRequest(): void
    {
        $factory = new DiscoveryQueryFactory();
        $request = new Request([
            'q' => 'symfony modernization',
            'resourceType' => 'offering',
            'status' => 'active',
            'visibility' => 'public',
            'limit' => '10',
            'offset' => '5',
        ]);

        $query = $factory->fromRequest($request);

        self::assertSame('symfony modernization', $query->term);
        self::assertSame('offering', $query->resourceType);
        self::assertSame('active', $query->status);
        self::assertSame('public', $query->visibility);
        self::assertSame(10, $query->limit);
        self::assertSame(5, $query->offset);
    }

    public function testItFallsBackForInvalidPaginationValues(): void
    {
        $factory = new DiscoveryQueryFactory();
        $request = new Request([
            'limit' => '-9',
            'offset' => 'oops',
        ]);

        $query = $factory->fromRequest($request);

        self::assertSame(25, $query->limit);
        self::assertSame(0, $query->offset);
    }
}
