<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Behavioral\Discovery;

use App\Discovering\Dto\Discovery\DiscoveryQuery;
use App\Discovering\Service\Discovery\DiscoveryHighlightingService;
use App\Discovering\Service\Discovery\DiscoveryLearningService;
use App\Discovering\Service\Discovery\DiscoveryModePresetService;
use App\Discovering\Service\Discovery\DiscoveryScoringService;
use App\Discovering\Service\Discovery\DiscoveryService;
use App\Discovering\Service\Discovery\DoctrineDiscoveryFeedbackStore;
use App\Discovering\ServiceInterface\Discovery\Backend\DiscoveryBackendInterface;
use App\Discovering\Tests\Support\DiscoveryDoctrineEntityManagerFactory;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;

/**
 * Exercises the discovery feedback learning behavior test case for the Discovering component.
 */
final class DiscoveryFeedbackLearningBehaviorTest extends DiscoveryTempFilesystemTestCase
{
    public function testRecordedClicksCanPromoteTrustedOperationalHitAbovePureFtsLeader(): void
    {
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $learningService = new DiscoveryLearningService(new DoctrineDiscoveryFeedbackStore($entityManager));
        $service = new DiscoveryService(
            $this->createBackend([
                [
                    'id' => 'project-recovery',
                    'title' => 'Recovery procedure note',
                    'resource' => 'project',
                    'reference' => 'project-recovery-procedure',
                    'status' => 'active',
                    'content' => 'Recovery procedure for discovery rollout validation.',
                    'ftsScore' => -0.01,
                ],
                [
                    'id' => 'playbook-recovery',
                    'title' => 'Recovery procedure note',
                    'resource' => 'playbook',
                    'reference' => 'playbook-recovery-procedure',
                    'status' => 'active',
                    'content' => 'Recovery procedure for discovery rollout validation.',
                    'ftsScore' => -5.00,
                ],
            ]),
            new DiscoveryScoringService(new DiscoveryHighlightingService(), $learningService),
            new DiscoveryModePresetService(),
        );

        $beforeLearning = $service->discover(new DiscoveryQuery(query: 'recovery procedure'));
        self::assertSame('project-recovery', $beforeLearning->hits[0]->id);
        self::assertSame(0, $beforeLearning->hits[0]->feedbackCount);
        self::assertSame(0.0, $beforeLearning->hits[0]->feedbackBoost);

        for ($i = 0; $i < 8; ++$i) {
            $learningService->recordUsefulClick(
                'playbook',
                'playbook-recovery',
                'Recovery procedure note',
                'playbook-recovery-procedure',
            );
        }

        $afterLearning = $service->discover(new DiscoveryQuery(query: 'recovery procedure'));

        self::assertSame('playbook-recovery', $afterLearning->hits[0]->id);
        self::assertSame(8, $afterLearning->hits[0]->feedbackCount);
        self::assertGreaterThan(9.0, $afterLearning->hits[0]->feedbackBoost);
        self::assertContains('feedback boost 9.51', $afterLearning->hits[0]->matchReasons);
        self::assertSame('project-recovery', $afterLearning->hits[1]->id);
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function createBackend(array $rows): DiscoveryBackendInterface
    {
        return new class($rows) implements DiscoveryBackendInterface {
            /** @param list<array<string, mixed>> $rows */
            public function __construct(private array $rows)
            {
            }

            public function upsert(string $resource, string $id, array $document): void
            {
            }

            public function remove(string $resource, string $id): void
            {
            }

            public function search(string $resource, string $query, int $limit = 20, int $offset = 0): array
            {
                return $this->rows;
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
    }
}
