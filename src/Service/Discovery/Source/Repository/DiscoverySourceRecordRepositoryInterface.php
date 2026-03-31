<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source\Repository;

use App\Dto\Discovery\DiscoverySourceRecord;

interface DiscoverySourceRecordRepositoryInterface
{
    public function getSourceName(): string;

    public function getResourceType(): string;

    /**
     * @return list<DiscoverySourceRecord>
     */
    public function all(): array;
}
