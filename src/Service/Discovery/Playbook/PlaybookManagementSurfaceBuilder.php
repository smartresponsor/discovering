<?php

declare(strict_types=1);

namespace App\Service\Discovery\Playbook;

use App\Dto\Discovery\PlaybookManagementSurface;
use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Support\DirectoryBackedFamilyManagementSurfaceBuilder;

final class PlaybookManagementSurfaceBuilder
{
    public function __construct(
        private readonly PlaybookFileDiscoverySourceRecordRepository $repository,
        private readonly DirectoryBackedFamilyManagementSurfaceBuilder $surfaceBuilder,
    ) {
    }

    public function build(): PlaybookManagementSurface
    {
        return PlaybookManagementSurface::fromGeneric($this->surfaceBuilder->build($this->repository));
    }
}
