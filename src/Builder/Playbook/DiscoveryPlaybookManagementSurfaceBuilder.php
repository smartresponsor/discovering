<?php

declare(strict_types=1);

namespace App\Discovering\Builder\Playbook;

use App\Discovering\Builder\Support\DiscoveryDirectoryBackedFamilyManagementSurfaceBuilder;
use App\Discovering\DTO\DiscoveryPlaybookManagementSurfaceDTO;
use App\Discovering\Repository\Source\DiscoveryPlaybookFileSourceRecordRepository;

/**
 * Builds the playbook management surface output used by discovery management or diagnostics flows.
 */
final class DiscoveryPlaybookManagementSurfaceBuilder
{
    public function __construct(
        private readonly DiscoveryPlaybookFileSourceRecordRepository $repository,
        private readonly DiscoveryDirectoryBackedFamilyManagementSurfaceBuilder $surfaceBuilder,
    ) {
    }

    /**
     * Builds the build result for this discovery workflow.
     */
    public function build(): DiscoveryPlaybookManagementSurfaceDTO
    {
        return DiscoveryPlaybookManagementSurfaceDTO::fromGeneric($this->surfaceBuilder->build($this->repository));
    }
}
