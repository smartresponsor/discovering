<?php
declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoveryHit;
use App\Dto\Discovery\DiscoveryQuery;
use App\Service\Discovery\DiscoveryHighlightingService;
use App\Service\Discovery\DiscoveryScoringService;
use PHPUnit\Framework\TestCase;

final class DiscoveryScoringServiceTest extends TestCase
{
    public function testItRanksHitsByPhraseTokensFtsAndResourceWeight(): void
    {
        $service = new DiscoveryScoringService(new DiscoveryHighlightingService());
        $hits = [
            new DiscoveryHit(
                id: 'briefing-1',
                title: 'Search portability briefing',
                resource: 'briefing',
                reference: 'briefing-search-portability',
                status: 'active',
                ftsScore: -0.8,
            ),
            new DiscoveryHit(
                id: 'playbook-1',
                title: 'Reindex operations playbook',
                resource: 'playbook',
                reference: 'playbook-reindex-operations',
                status: 'active',
                ftsScore: -0.1,
            ),
            new DiscoveryHit(
                id: 'playbook-2',
                title: 'Governance audit playbook',
                resource: 'playbook',
                reference: 'playbook-governance-audit',
                status: 'draft',
                ftsScore: null,
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
        self::assertContains('fts boost 5.56', $rankedHits[0]->matchReasons);
        self::assertSame(['search', 'portability'], $rankedHits[0]->matchedTokens);
        self::assertStringContainsString('<mark>Search</mark>', $rankedHits[0]->highlightedTitle);
        self::assertStringContainsString('<mark>search</mark>', strtolower($rankedHits[0]->highlightedReference));
        self::assertSame('playbook-1', $rankedHits[1]->id);
        self::assertSame(0.0, $rankedHits[2]->score);
    }

    public function testItAppliesFiltersBeforeRanking(): void
    {
        $service = new DiscoveryScoringService(new DiscoveryHighlightingService());
        $hits = [
            new DiscoveryHit(id: 'playbook-active', title: 'Governance playbook', resource: 'playbook', status: 'active', ftsScore: -0.2),
            new DiscoveryHit(id: 'playbook-draft', title: 'Governance draft', resource: 'playbook', status: 'draft', ftsScore: -0.2),
            new DiscoveryHit(id: 'briefing-active', title: 'Governance briefing', resource: 'briefing', status: 'active', ftsScore: -0.2),
        ];

        $query = new DiscoveryQuery(
            query: 'governance',
            filters: ['status' => 'active', 'resource' => 'playbook'],
        );

        $rankedHits = $service->rank($hits, $query);

        self::assertCount(1, $rankedHits);
        self::assertSame('playbook-active', $rankedHits[0]->id);
        self::assertGreaterThan(0.0, $rankedHits[0]->score);
        self::assertSame(['governance'], $rankedHits[0]->matchedTokens);
    }
}
