<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source;

use App\Service\Discovery\Source\Repository\CategoryDiscoverySourceRecordRepository;
use App\ServiceInterface\Discovery\Source\DiscoverySourceProviderInterface;

final class CategoryDiscoverySourceProvider implements DiscoverySourceProviderInterface
{
    public function __construct(
        private readonly CategoryDiscoverySourceRecordRepository $repository,
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
