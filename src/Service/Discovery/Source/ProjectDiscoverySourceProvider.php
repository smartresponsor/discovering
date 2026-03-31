<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source;

use App\Service\Discovery\Source\Repository\ProjectDiscoverySourceRecordRepository;
use App\ServiceInterface\Discovery\Source\DiscoverySourceProviderInterface;

final class ProjectDiscoverySourceProvider implements DiscoverySourceProviderInterface
{
    public function __construct(
        private readonly ProjectDiscoverySourceRecordRepository $repository,
    ) {
    }

    public function getSourceName(): string
    {
        return $this->repository->getSourceName();
    }

    public function getResourceType(): string
    {
        return $this->repository->getResourceType();
    }

    public function provide(): array
    {
        return $this->repository->all();
    }
}
