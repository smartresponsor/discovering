<?php

declare(strict_types=1);

namespace App\Discovering\Service;

use App\Discovering\DTO\DiscoveryModeDTO;
use App\Discovering\DTO\DiscoveryQueryDTO;

/**
 * Provides the discovery mode preset capability within the discovery component.
 */
final class DiscoveryModePresetService
{
    /**
     * Performs the apply operation for this discovery service.
     */
    public function apply(DiscoveryQueryDTO $query): DiscoveryQueryDTO
    {
        $presetWeights = $this->presetWeights($query->mode);
        $presetFilters = $this->presetFilters($query->mode);

        return new DiscoveryQueryDTO(
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
            DiscoveryModeDTO::GOVERNANCE => [
                'briefing' => 1.35,
                'document' => 1.25,
                'playbook' => 0.95,
            ],
            DiscoveryModeDTO::OPERATIONS => [
                'playbook' => 1.45,
                'document' => 1.10,
                'briefing' => 0.90,
            ],
            DiscoveryModeDTO::EXPLORATION => [
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
            DiscoveryModeDTO::GOVERNANCE, DiscoveryModeDTO::OPERATIONS => ['status' => 'active'],
            default => [],
        };
    }
}
