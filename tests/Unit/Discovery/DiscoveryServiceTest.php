<?php
declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoveryQuery;
use App\Service\Discovery\DiscoveryHighlightingService;
use App\Service\Discovery\DiscoveryScoringService;
use App\Service\Discovery\DiscoveryService;
use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use PHPUnit\Framework\TestCase;

final class DiscoveryServiceTest extends TestCase
{
    public function testItRanksAndPaginatesDiscoveryHitsAfterAdapterSearch(): void
    {
        $adapter = new class() implements DiscoveryAdapterInterface {
            public function upsert(string $resource, string $id, array $document): void
            {
            }

            public function remove(string $resource, string $id): void
            {
            }

            public function search(string $resource, string $query, int $limit = 20, int $offset = 0): array
            {
                return [
                    ['id' => 'playbook-1', 'title' => 'Reindex operations playbook', 'resource' => 'playbook', 'reference' => 'playbook-reindex-operations', 'status' => 'active', 'ftsScore' => -0.2],
                    ['id' => 'briefing-1', 'title' => 'Search portability briefing', 'resource' => 'briefing', 'reference' => 'briefing-search-portability', 'status' => 'active', 'ftsScore' => -0.9],
                    ['id' => 'briefing-2', 'title' => 'Governance review briefing', 'resource' => 'briefing', 'reference' => 'briefing-governance-review', 'status' => 'active', 'ftsScore' => -0.4],
                ];
            }

            public function createIndex(string $resource): void
            {
            }

            public function swapAlias(string $from, string $to): void
            {
            }
        };

        $service = new DiscoveryService($adapter, new DiscoveryScoringService(new DiscoveryHighlightingService()));
        $result = $service->discover(new DiscoveryQuery(
            query: 'briefing governance',
            limit: 1,
            offset: 1,
            resourceWeights: ['briefing' => 1.2],
        ));

        self::assertSame(2, $result->total);
        self::assertCount(1, $result->hits);
        self::assertSame('briefing-2', $result->hits[0]->id);
        self::assertGreaterThan(0.0, $result->hits[0]->score);
        self::assertContains('resource weight 1.20', $result->hits[0]->matchReasons);
        self::assertNotNull($result->hits[0]->ftsScore);
        self::assertSame(['briefing', 'governance'], $result->hits[0]->matchedTokens);
        self::assertStringContainsString('<mark>briefing</mark>', strtolower($result->hits[0]->highlightedTitle));
    }
}
