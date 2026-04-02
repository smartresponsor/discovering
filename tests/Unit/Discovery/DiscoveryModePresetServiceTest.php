<?php
declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoveryMode;
use App\Dto\Discovery\DiscoveryQuery;
use App\Service\Discovery\DiscoveryModePresetService;
use PHPUnit\Framework\TestCase;

final class DiscoveryModePresetServiceTest extends TestCase
{
    public function testItAppliesGovernancePresetWeightsAndStatusFilter(): void
    {
        $service = new DiscoveryModePresetService();
        $query = new DiscoveryQuery(query: 'audit', mode: DiscoveryMode::GOVERNANCE);

        $effective = $service->apply($query);

        self::assertSame(DiscoveryMode::GOVERNANCE, $effective->mode);
        self::assertSame('active', $effective->filters['status']);
        self::assertSame(1.35, $effective->resourceWeights['briefing']);
        self::assertSame(1.25, $effective->resourceWeights['document']);
    }

    public function testExplicitWeightsAndFiltersOverridePresetValues(): void
    {
        $service = new DiscoveryModePresetService();
        $query = new DiscoveryQuery(
            query: 'operations',
            mode: DiscoveryMode::OPERATIONS,
            filters: ['status' => 'draft'],
            resourceWeights: ['playbook' => 2.0],
        );

        $effective = $service->apply($query);

        self::assertSame('draft', $effective->filters['status']);
        self::assertSame(2.0, $effective->resourceWeights['playbook']);
        self::assertSame(1.10, $effective->resourceWeights['document']);
    }
}
