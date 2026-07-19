<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Briefing;

use App\Discovering\Dto\Discovery\BriefingManagementSurface;
use App\Discovering\Service\Discovery\Source\Repository\BriefingFileDiscoverySourceRecordRepository;
use App\Discovering\Service\Discovery\Support\DirectoryBackedFamilyManagementSurfaceBuilder;

/**
 * Builds the briefing management surface output used by discovery management or diagnostics flows.
 */
final class BriefingManagementSurfaceBuilder
{
    public function __construct(
        private readonly BriefingFileDiscoverySourceRecordRepository $repository,
        private readonly DirectoryBackedFamilyManagementSurfaceBuilder $surfaceBuilder,
    ) {
    }

    /**
     * Builds the build result for this discovery workflow.
     */
    public function build(): BriefingManagementSurface
    {
        return BriefingManagementSurface::fromGeneric($this->surfaceBuilder->build($this->repository));
    }
}
