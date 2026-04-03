<?php
declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoveryHit;
use App\Dto\Discovery\DiscoveryQuery;
use App\Service\Discovery\DiscoveryFeedbackStore;
use App\Service\Discovery\DiscoveryHighlightingService;
use App\Service\Discovery\DiscoveryLearningService;
use App\Service\Discovery\DiscoveryScoringService;
use App\Tests\Support\DiscoveryTempFilesystemTestCase;

final class DiscoveryScoringServiceTest extends DiscoveryTempFilesystemTestCase
{
    public function testItRanksHitsAndAppliesFeedbackBoost(): void
    {
        $path = $this->createTempFilePath('discovering-scoring-feedback-', '.sqlite');
        $learningService = new DiscoveryLearningService(new DiscoveryFeedbackStore($path));
        $learningService->recordUsefulClick('playbook', 'playbook-1', 'Reindex operations playbook', 'playbook-reindex-operations');
        $learningService->recordUsefulClick('playbook', 'playbook-1', 'Reindex operations playbook', 'playbook-reindex-operations');

        $service = new DiscoveryScoringService(new DiscoveryHighlightingService(), $learningService);
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

        $query = new DiscoveryQuery(query: 'reindex operations');
        $rankedHits = $service->rank($hits, $query);

        self::assertSame('playbook-1', $rankedHits[0]->id);
        self::assertSame(2, $rankedHits[0]->feedbackCount);
        self::assertGreaterThan(0.0, $rankedHits[0]->feedbackBoost);
        self::assertContains('feedback boost 4.75', $rankedHits[0]->matchReasons);

        @unlink($path);
    }
}
