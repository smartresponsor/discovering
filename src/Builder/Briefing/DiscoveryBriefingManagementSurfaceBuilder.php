<?php

declare(strict_types=1);

namespace App\Discovering\Builder\Briefing;

use App\Discovering\Builder\Support\DiscoveryDirectoryBackedFamilyManagementSurfaceBuilder;
use App\Discovering\DTO\DiscoveryBriefingManagementSurfaceDTO;
use App\Discovering\Repository\Source\DiscoveryBriefingFileSourceRecordRepository;

/**
 * Builds the briefing management surface output used by discovery management or diagnostics flows.
 */
final class DiscoveryBriefingManagementSurfaceBuilder
{
    public function __construct(
        private readonly DiscoveryBriefingFileSourceRecordRepository $repository,
        private readonly DiscoveryDirectoryBackedFamilyManagementSurfaceBuilder $surfaceBuilder,
    ) {
    }

    /**
     * Builds the build result for this discovery workflow.
     */
    public function build(): DiscoveryBriefingManagementSurfaceDTO
    {
        return DiscoveryBriefingManagementSurfaceDTO::fromGeneric($this->surfaceBuilder->build($this->repository));
    }
}
