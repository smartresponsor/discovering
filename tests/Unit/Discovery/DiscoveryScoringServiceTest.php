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
    public function testItRanksHitsAndBuildsHighlightsAndSnippets(): void
    {
        $service = new DiscoveryScoringService(new DiscoveryHighlightingService());
        $hits = [
            new DiscoveryHit(
                id: 'briefing-1',
                title: 'Search portability briefing',
                resource: 'briefing',
                reference: 'briefing-search-portability',
                status: 'active',
                content: 'This briefing explains search portability and governance expectations for discovery.',
                ftsScore: -0.8,
            ),
            new DiscoveryHit(
                id: 'playbook-1',
                title: 'Reindex operations playbook',
                resource: 'playbook',
                reference: 'playbook-reindex-operations',
                status: 'active',
                content: 'Operational playbook for reindex validation and recovery.',
                ftsScore: -0.1,
            ),
        ];

        $query = new DiscoveryQuery(
            query: 'search portability',
            resourceWeights: ['briefing' => 1.3],
        );

        $rankedHits = $service->rank($hits, $query);

        self::assertCount(2, $rankedHits);
        self::assertSame('briefing-1', $rankedHits[0]->id);
        self::assertContains('content token: search', $rankedHits[0]->matchReasons);
        self::assertContains('fts boost 5.56', $rankedHits[0]->matchReasons);
        self::assertSame(['search', 'portability'], $rankedHits[0]->matchedTokens);
        self::assertStringContainsString('<mark>Search</mark>', $rankedHits[0]->highlightedTitle);
        self::assertStringContainsString('<mark>search</mark>', strtolower($rankedHits[0]->highlightedSnippet));
    }
}
