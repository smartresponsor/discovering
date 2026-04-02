<?php
declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoveryMode;
use App\Dto\Discovery\DiscoveryQuery;
use App\Service\Discovery\DiscoveryHighlightingService;
use App\Service\Discovery\DiscoveryModePresetService;
use App\Service\Discovery\DiscoveryScoringService;
use App\Service\Discovery\DiscoveryService;
use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use PHPUnit\Framework\TestCase;

final class DiscoveryServiceTest extends TestCase
{
    public function testItBuildsRankedHitsWithHighlightsAndSnippets(): void
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
            new DiscoveryScoringService(new DiscoveryHighlightingService()),
            new DiscoveryModePresetService(),
        );

        $result = $service->discover(new DiscoveryQuery(
            query: 'governance review',
            mode: DiscoveryMode::GOVERNANCE,
        ));

        self::assertSame(1, $result->total);
        self::assertSame('briefing-2', $result->hits[0]->id);
        self::assertSame(['governance', 'review'], $result->hits[0]->matchedTokens);
        self::assertStringContainsString('<mark>Governance</mark>', $result->hits[0]->highlightedTitle);
        self::assertStringContainsString('<mark>governance</mark>', strtolower($result->hits[0]->highlightedSnippet));
    }
}
