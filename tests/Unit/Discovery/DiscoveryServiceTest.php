<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Dto\Discovery\DiscoveryMode;
use App\Discovering\Dto\Discovery\DiscoveryQuery;
use App\Discovering\Service\Discovery\DiscoveryHighlightingService;
use App\Discovering\Service\Discovery\DiscoveryLearningService;
use App\Discovering\Service\Discovery\DiscoveryModePresetService;
use App\Discovering\Service\Discovery\DiscoveryScoringService;
use App\Discovering\Service\Discovery\DiscoveryService;
use App\Discovering\Service\Discovery\DoctrineDiscoveryFeedbackStore;
use App\Discovering\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use App\Discovering\Tests\Support\DiscoveryDoctrineEntityManagerFactory;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;

/**
 * Exercises the discovery service test case for the Discovering component.
 */
final class DiscoveryServiceTest extends DiscoveryTempFilesystemTestCase
{
    public function testItBuildsRankedHitsWithFeedbackAwareBoosting(): void
    {
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $learningService = new DiscoveryLearningService(new DoctrineDiscoveryFeedbackStore($entityManager));
        $learningService->recordUsefulClick('briefing', 'briefing-2', 'Governance review briefing', 'briefing-governance-review');
        $learningService->recordUsefulClick('briefing', 'briefing-2', 'Governance review briefing', 'briefing-governance-review');

        $adapter = new class implements DiscoveryAdapterInterface {
            public function upsert(string $resource, string $id, array $document): void
            {
            }

            public function remove(string $resource, string $id): void
            {
            }

            public function search(string $resource, string $query, int $limit = 20, int $offset = 0): array
            {
                return [
                    [
                        'id' => 'briefing-2',
                        'title' => 'Governance review briefing',
                        'resource' => 'briefing',
                        'reference' => 'briefing-governance-review',
                        'status' => 'active',
                        'content' => 'Governance review briefing for operators validating discovery merge readiness and audit posture.',
                        'ftsScore' => -0.4,
                    ],
                    [
                        'id' => 'playbook-1',
                        'title' => 'Reindex operations playbook',
                        'resource' => 'playbook',
                        'reference' => 'playbook-reindex-operations',
                        'status' => 'active',
                        'content' => 'Operational playbook for reindex and validation.',
                        'ftsScore' => -0.2,
                    ],
                ];
            }

            public function createIndex(string $resource): void
            {
            }

            public function swapAlias(string $from, string $to): void
            {
            }

            public function getBackendName(): string
            {
                return 'behavioral-fixture';
            }
        };

        $service = new DiscoveryService(
            $adapter,
            new DiscoveryScoringService(new DiscoveryHighlightingService(), $learningService),
            new DiscoveryModePresetService(),
        );

        $result = $service->discover(new DiscoveryQuery(
            query: 'governance review',
            mode: DiscoveryMode::GOVERNANCE,
        ));

        self::assertSame(2, $result->total);
        self::assertSame('briefing-2', $result->hits[0]->id);
        self::assertSame(2, $result->hits[0]->feedbackCount);
        self::assertGreaterThan(0.0, $result->hits[0]->feedbackBoost);
        self::assertContains('feedback boost 4.75', $result->hits[0]->matchReasons);
    }
}
