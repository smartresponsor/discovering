<?php
declare(strict_types=1);

namespace App\Service\Discovery;

use App\Dto\Discovery\DiscoveryMode;
use App\Dto\Discovery\DiscoveryQuery;


/**
 * Provides the discovery mode preset capability within the discovery component.
 */
final class DiscoveryModePresetService
{
    /**
     * Performs the apply operation for this discovery service.
     */
    public function apply(DiscoveryQuery $query): DiscoveryQuery
    {
        $presetWeights = $this->presetWeights($query->mode);
        $presetFilters = $this->presetFilters($query->mode);

        return new DiscoveryQuery(
            query: $query->query,
            resource: $query->resource,
            limit: $query->limit,
            offset: $query->offset,
            filters: array_replace($presetFilters, $query->filters),
            resourceWeights: array_replace($presetWeights, $query->resourceWeights),
            mode: $query->mode,
        );
    }

    /**
     * @return array<string, float>
     */
    private function presetWeights(string $mode): array
    {
        return match ($mode) {
            DiscoveryMode::GOVERNANCE => [
                'briefing' => 1.35,
                'document' => 1.25,
                'playbook' => 0.95,
            ],
            DiscoveryMode::OPERATIONS => [
                'playbook' => 1.45,
                'document' => 1.10,
                'briefing' => 0.90,
            ],
            DiscoveryMode::EXPLORATION => [
                'project' => 1.15,
                'offering' => 1.15,
                'document' => 1.15,
                'playbook' => 1.15,
                'briefing' => 1.15,
            ],
            default => [],
        };
    }

    /**
     * @return array<string, scalar|null>
     */
    private function presetFilters(string $mode): array
    {
        return match ($mode) {
            DiscoveryMode::GOVERNANCE, DiscoveryMode::OPERATIONS => ['status' => 'active'],
            default => [],
        };
    }
}
