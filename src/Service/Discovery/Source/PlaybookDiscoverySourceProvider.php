<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source;

use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use App\ServiceInterface\Discovery\Source\DiscoverySourceProviderInterface;

final class PlaybookDiscoverySourceProvider implements DiscoverySourceProviderInterface
{
    public function __construct(
        private readonly PlaybookFileDiscoverySourceRecordRepository $repository,
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
