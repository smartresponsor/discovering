<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\DTO\DiscoveryModeDTO;
use App\Discovering\DTO\DiscoveryQueryDTO;
use App\Discovering\Service\DiscoveryModePresetService;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the discovery mode preset service test case for the Discovering component.
 */
final class DiscoveryModePresetServiceTest extends TestCase
{
    public function testItAppliesGovernancePresetWeightsAndStatusFilter(): void
    {
        $service = new DiscoveryModePresetService();
        $query = new DiscoveryQueryDTO(query: 'audit', mode: DiscoveryModeDTO::GOVERNANCE);

        $effective = $service->apply($query);

        self::assertSame(DiscoveryModeDTO::GOVERNANCE, $effective->mode);
        self::assertSame('active', $effective->filters['status']);
        self::assertSame(1.35, $effective->resourceWeights['briefing']);
        self::assertSame(1.25, $effective->resourceWeights['document']);
    }

    public function testExplicitWeightsAndFiltersOverridePresetValues(): void
    {
        $service = new DiscoveryModePresetService();
        $query = new DiscoveryQueryDTO(
            query: 'operations',
            mode: DiscoveryModeDTO::OPERATIONS,
            filters: ['status' => 'draft'],
            resourceWeights: ['playbook' => 2.0],
        );

        $effective = $service->apply($query);

        self::assertSame('draft', $effective->filters['status']);
        self::assertSame(2.0, $effective->resourceWeights['playbook']);
        self::assertSame(1.10, $effective->resourceWeights['document']);
    }
}
