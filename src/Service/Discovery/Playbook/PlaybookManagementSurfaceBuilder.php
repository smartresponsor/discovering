<?php

declare(strict_types=1);

namespace App\Service\Discovery\Playbook;

use App\Dto\Discovery\PlaybookManagementSurface;
use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Support\DirectoryBackedFamilyManagementSurfaceBuilder;


/**
 * Builds the playbook management surface output used by discovery management or diagnostics flows.
 */
final class PlaybookManagementSurfaceBuilder
{
    public function __construct(
        private readonly PlaybookFileDiscoverySourceRecordRepository $repository,
        private readonly DirectoryBackedFamilyManagementSurfaceBuilder $surfaceBuilder,
    ) {
    }

    /**
     * Builds the build result for this discovery workflow.
     */
    public function build(): PlaybookManagementSurface
    {
        return PlaybookManagementSurface::fromGeneric($this->surfaceBuilder->build($this->repository));
    }
}
