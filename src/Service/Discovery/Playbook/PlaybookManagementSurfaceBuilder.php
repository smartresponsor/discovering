<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Playbook;

use App\Discovering\Dto\Discovery\PlaybookManagementSurface;
use App\Discovering\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use App\Discovering\Service\Discovery\Support\DirectoryBackedFamilyManagementSurfaceBuilder;

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
