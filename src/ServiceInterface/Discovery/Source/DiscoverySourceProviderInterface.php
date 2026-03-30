<?php

declare(strict_types=1);

namespace App\ServiceInterface\Discovery\Source;

use App\Dto\Discovery\DiscoverySourceRecord;

interface DiscoverySourceProviderInterface
{
    public function getResourceType(): string;

    /**
     * @return list<DiscoverySourceRecord>
     */
    public function provide(): array;
}
