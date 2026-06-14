<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source;

use App\Service\Discovery\Source\Repository\CategoryDiscoverySourceRecordRepository;
use App\ServiceInterface\Discovery\Source\DiscoverySourceProviderInterface;

/**
 * Provides the category discovery source provider capability within the discovery component.
 */
final class CategoryDiscoverySourceProvider implements DiscoverySourceProviderInterface
{
    public function __construct(
        private readonly CategoryDiscoverySourceRecordRepository $repository,
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
