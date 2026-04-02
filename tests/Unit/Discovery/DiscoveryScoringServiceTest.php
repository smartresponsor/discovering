<?php
declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoveryHit;
use App\Dto\Discovery\DiscoveryQuery;
use App\Service\Discovery\DiscoveryScoringService;
use PHPUnit\Framework\TestCase;

final class DiscoveryScoringServiceTest extends TestCase
{
    public function testItRanksHitsByPhraseTokensAndResourceWeight(): void
    {
        $service = new DiscoveryScoringService();
        $hits = [
            new DiscoveryHit(
                id: 'briefing-1',
                title: 'Search portability briefing',
                resource: 'briefing',
                reference: 'briefing-search-portability',
                status: 'active',
            ),
            new DiscoveryHit(
                id: 'playbook-1',
                title: 'Reindex operations playbook',
                resource: 'playbook',
                reference: 'playbook-reindex-operations',
                status: 'active',
            ),
            new DiscoveryHit(
                id: 'playbook-2',
                title: 'Governance audit playbook',
                resource: 'playbook',
                reference: 'playbook-governance-audit',
                status: 'draft',
            ),
        ];

        $query = new DiscoveryQuery(
            query: 'search portability',
            resourceWeights: ['briefing' => 1.3],
        );

        $rankedHits = $service->rank($hits, $query);

        self::assertCount(3, $rankedHits);
        self::assertSame('briefing-1', $rankedHits[0]->id);
        self::assertGreaterThan(0.0, $rankedHits[0]->score);
        self::assertContains('title phrase match', $rankedHits[0]->matchReasons);
        self::assertContains('resource weight 1.30', $rankedHits[0]->matchReasons);
        self::assertSame('playbook-1', $rankedHits[1]->id);
        self::assertSame(0.0, $rankedHits[2]->score);
    }

    public function testItAppliesFiltersBeforeRanking(): void
    {
        $service = new DiscoveryScoringService();
        $hits = [
            new DiscoveryHit(id: 'playbook-active', title: 'Governance playbook', resource: 'playbook', status: 'active'),
            new DiscoveryHit(id: 'playbook-draft', title: 'Governance draft', resource: 'playbook', status: 'draft'),
            new DiscoveryHit(id: 'briefing-active', title: 'Governance briefing', resource: 'briefing', status: 'active'),
        ];

        $query = new DiscoveryQuery(
            query: 'governance',
            filters: ['status' => 'active', 'resource' => 'playbook'],
        );

        $rankedHits = $service->rank($hits, $query);

        self::assertCount(1, $rankedHits);
        self::assertSame('playbook-active', $rankedHits[0]->id);
        self::assertGreaterThan(0.0, $rankedHits[0]->score);
    }
}
