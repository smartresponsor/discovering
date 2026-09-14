<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Dto\Discovery\DiscoveryHit;
use App\Discovering\Dto\Discovery\DiscoveryQuery;
use App\Discovering\Service\Discovery\DiscoveryHighlightingService;
use App\Discovering\Service\Discovery\DiscoveryLearningService;
use App\Discovering\Service\Discovery\DiscoveryScoringService;
use App\Discovering\Service\Discovery\DoctrineDiscoveryFeedbackStore;
use App\Discovering\Tests\Support\DiscoveryDoctrineEntityManagerFactory;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;

/**
 * Exercises the discovery scoring service test case for the Discovering component.
 */
final class DiscoveryScoringServiceTest extends DiscoveryTempFilesystemTestCase
{
    public function testItRanksHitsAndAppliesFeedbackBoost(): void
    {
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $learningService = new DiscoveryLearningService(new DoctrineDiscoveryFeedbackStore($entityManager));
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
    }
}
