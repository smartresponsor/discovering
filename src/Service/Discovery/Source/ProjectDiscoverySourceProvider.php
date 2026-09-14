<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Source;

use App\Discovering\Service\Discovery\Source\Repository\ProjectDiscoverySourceRecordRepository;
use App\Discovering\ServiceInterface\Discovery\Source\DiscoverySourceProviderInterface;

/**
 * Provides the project discovery source provider capability within the discovery component.
 */
final class ProjectDiscoverySourceProvider implements DiscoverySourceProviderInterface
{
    public function __construct(
        private readonly ProjectDiscoverySourceRecordRepository $repository,
    ) {
    }

    /**
     * Returns the source nameEntity value exposed by this service.
     */
    public function getSourceName(): string
    {
        return $this->repository->getSourceName();
    }

    /**
     * Returns the resource type value exposed by this service.
     */
    public function getResourceType(): string
    {
        return $this->repository->getResourceType();
    }

    /**
     * Performs the provide operation for this discovery service.
     */
    public function provide(): array
    {
        return $this->repository->all();
    }
}
