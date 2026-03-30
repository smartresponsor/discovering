<?php

declare(strict_types=1);

namespace App\ServiceInterface\Discovery\Adapter;

use App\Dto\Discovery\DiscoveryQuery;
use App\Dto\Discovery\DiscoveryResult;

interface DiscoveryAdapterInterface
{
    public function discover(DiscoveryQuery $query): DiscoveryResult;

    public function rebuild(?string $resourceType = null): void;

    public function getBackendName(): string;
}
