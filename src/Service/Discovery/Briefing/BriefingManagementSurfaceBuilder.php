<?php

declare(strict_types=1);

namespace App\Service\Discovery\Briefing;

use App\Dto\Discovery\BriefingManagementSurface;
use App\Service\Discovery\Source\Repository\BriefingFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Support\DirectoryBackedFamilyManagementSurfaceBuilder;


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
