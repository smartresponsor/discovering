<?php

declare(strict_types=1);

namespace App\Discovering\Provider\Source;

use App\Discovering\Repository\Source\DiscoveryPlaybookFileSourceRecordRepository;
use App\Discovering\ServiceInterface\Source\DiscoverySourceProviderInterface;

/**
 * Provides the playbook discovery source provider capability within the discovery component.
 */
final class DiscoveryPlaybookSourceProvider implements DiscoverySourceProviderInterface
{
    public function __construct(
        private readonly DiscoveryPlaybookFileSourceRecordRepository $repository,
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
