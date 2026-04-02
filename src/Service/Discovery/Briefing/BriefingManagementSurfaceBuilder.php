<?php

declare(strict_types=1);

namespace App\Service\Discovery\Briefing;

use App\Dto\Discovery\BriefingManagementSurface;
use App\Service\Discovery\Source\Repository\BriefingFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Support\DirectoryBackedFamilyManagementSurfaceBuilder;

final class BriefingManagementSurfaceBuilder
{
    public function __construct(
        private readonly BriefingFileDiscoverySourceRecordRepository $repository,
        private readonly DirectoryBackedFamilyManagementSurfaceBuilder $surfaceBuilder,
    ) {
    }

    public function build(): BriefingManagementSurface
    {
        return BriefingManagementSurface::fromGeneric($this->surfaceBuilder->build($this->repository));
    }
}
