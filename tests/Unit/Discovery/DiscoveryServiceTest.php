<?php
declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoveryMode;
use App\Dto\Discovery\DiscoveryQuery;
use App\Service\Discovery\DiscoveryFeedbackStore;
use App\Service\Discovery\DiscoveryHighlightingService;
use App\Service\Discovery\DiscoveryLearningService;
use App\Service\Discovery\DiscoveryModePresetService;
use App\Service\Discovery\DiscoveryScoringService;
use App\Service\Discovery\DiscoveryService;
use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use App\Tests\Support\DiscoveryTempFilesystemTestCase;

final class DiscoveryServiceTest extends DiscoveryTempFilesystemTestCase
{
    public function testItBuildsRankedHitsWithFeedbackAwareBoosting(): void
    {
        $path = $this->createTempFilePath('discovering-service-feedback-', '.sqlite');
        $learningService = new DiscoveryLearningService(new DiscoveryFeedbackStore($path));
        $learningService->recordUsefulClick('briefing', 'briefing-2', 'Governance review briefing', 'briefing-governance-review');
        $learningService->recordUsefulClick('briefing', 'briefing-2', 'Governance review briefing', 'briefing-governance-review');

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

        self::assertSame(1, $result->total);
        self::assertSame('briefing-2', $result->hits[0]->id);
        self::assertSame(2, $result->hits[0]->feedbackCount);
        self::assertGreaterThan(0.0, $result->hits[0]->feedbackBoost);
        self::assertContains('feedback boost 4.75', $result->hits[0]->matchReasons);

        @unlink($path);
    }
}
