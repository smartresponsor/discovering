<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source;

use App\Service\Discovery\Source\Repository\DocumentDiscoverySourceRecordRepository;
use App\ServiceInterface\Discovery\Source\DiscoverySourceProviderInterface;


/**
 * Provides the document discovery source provider capability within the discovery component.
 */
final class DocumentDiscoverySourceProvider implements DiscoverySourceProviderInterface
{
    public function __construct(
        private readonly DocumentDiscoverySourceRecordRepository $repository,
    ) {
    }

    /**
     * Returns the source name value exposed by this service.
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
