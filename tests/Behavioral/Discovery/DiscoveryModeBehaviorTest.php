<?php

declare(strict_types=1);

namespace App\Tests\Behavioral\Discovery;

use App\Dto\Discovery\DiscoveryMode;
use App\Dto\Discovery\DiscoveryQuery;
use App\Service\Discovery\DiscoveryHighlightingService;
use App\Service\Discovery\DiscoveryLearningService;
use App\Service\Discovery\DiscoveryModePresetService;
use App\Service\Discovery\DiscoveryScoringService;
use App\Service\Discovery\DiscoveryService;
use App\Service\Discovery\DoctrineDiscoveryFeedbackStore;
use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use App\Tests\Support\DiscoveryDoctrineEntityManagerFactory;
use App\Tests\Support\DiscoveryTempFilesystemTestCase;

/**
 * Exercises the discovery mode behavior test case for the Discovering component.
 */
final class DiscoveryModeBehaviorTest extends DiscoveryTempFilesystemTestCase
{
    public function testGovernanceModePrefersActiveBriefingOverActivePlaybookAndExcludesDrafts(): void
    {
        $service = $this->createService([
            [
                'id' => 'playbook-draft',
                'title' => 'Audit readiness playbook',
                'resource' => 'playbook',
                'reference' => 'playbook-audit-readiness-draft',
                'status' => 'draft',
                'content' => 'Audit readiness guidance for discovery operators and governance review.',
                'ftsScore' => -0.01,
            ],
            [
                'id' => 'briefing-active',
                'title' => 'Audit readiness briefing',
                'resource' => 'briefing',
                'reference' => 'briefing-audit-readiness',
                'status' => 'active',
                'content' => 'Audit readiness guidance for discovery operators and governance review.',
                'ftsScore' => -0.10,
            ],
            [
                'id' => 'playbook-active',
                'title' => 'Audit readiness playbook',
                'resource' => 'playbook',
                'reference' => 'playbook-audit-readiness',
                'status' => 'active',
                'content' => 'Audit readiness guidance for discovery operators and governance review.',
                'ftsScore' => -0.10,
            ],
        ]);

        $result = $service->discover(new DiscoveryQuery(
            query: 'audit readiness',
            mode: DiscoveryMode::GOVERNANCE,
        ));

        self::assertSame(2, $result->total);
        self::assertSame('briefing-active', $result->hits[0]->id);
        self::assertSame('active', $result->hits[0]->status);
        self::assertSame('playbook-active', $result->hits[1]->id);
        self::assertContains('resource weight 1.35', $result->hits[0]->matchReasons);
        self::assertContains('resource weight 0.95', $result->hits[1]->matchReasons);
        self::assertSame('active', $result->query->filters['status']);
    }

    public function testOperationsModePrefersPlaybookOverBriefingForTheSameQueryShape(): void
    {
        $service = $this->createService([
            [
                'id' => 'briefing-active',
                'title' => 'Recovery drill runbook briefing',
                'resource' => 'briefing',
                'reference' => 'briefing-recovery-drill',
                'status' => 'active',
                'content' => 'Recovery drill procedure for discovery operators.',
                'ftsScore' => -0.10,
            ],
            [
                'id' => 'playbook-active',
                'title' => 'Recovery drill runbook playbook',
                'resource' => 'playbook',
                'reference' => 'playbook-recovery-drill',
                'status' => 'active',
                'content' => 'Recovery drill procedure for discovery operators.',
                'ftsScore' => -0.10,
            ],
        ]);

        $result = $service->discover(new DiscoveryQuery(
            query: 'recovery drill',
            mode: DiscoveryMode::OPERATIONS,
        ));

        self::assertSame(2, $result->total);
        self::assertSame('playbook-active', $result->hits[0]->id);
        self::assertContains('resource weight 1.45', $result->hits[0]->matchReasons);
        self::assertContains('resource weight 0.90', $result->hits[1]->matchReasons);
        self::assertSame('active', $result->query->filters['status']);
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function createService(array $rows): DiscoveryService
    {
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $learningService = new DiscoveryLearningService(new DoctrineDiscoveryFeedbackStore($entityManager));

        return new DiscoveryService(
            new class($rows) implements DiscoveryAdapterInterface {
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
            },
            new DiscoveryScoringService(new DiscoveryHighlightingService(), $learningService),
            new DiscoveryModePresetService(),
        );
    }
}
