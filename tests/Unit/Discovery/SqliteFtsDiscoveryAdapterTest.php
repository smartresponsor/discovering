<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoveryQuery;
use App\Service\Discovery\Adapter\SqliteFtsDiscoveryAdapter;
use App\Service\Discovery\Document\StaticDiscoveryDocumentProvider;
use App\Service\Discovery\Support\DiscoveryDocumentMatcher;
use PHPUnit\Framework\TestCase;

final class SqliteFtsDiscoveryAdapterTest extends TestCase
{
    public function testItFindsDiscoveryWorkspaceProject(): void
    {
        $adapter = new SqliteFtsDiscoveryAdapter(
            new StaticDiscoveryDocumentProvider(),
            new DiscoveryDocumentMatcher(),
        );

        $result = $adapter->discover(new DiscoveryQuery(term: 'workspace rollout'));

        self::assertGreaterThanOrEqual(1, $result->total);
        self::assertSame('project', $result->hits[0]->resourceType);
    }
}
