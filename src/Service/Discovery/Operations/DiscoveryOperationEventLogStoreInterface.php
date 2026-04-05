<?php

declare(strict_types=1);

namespace App\Service\Discovery\Operations;

use App\Dto\Discovery\DiscoveryOperationEvent;

interface DiscoveryOperationEventLogStoreInterface
{
    public function append(DiscoveryOperationEvent $event): void;

    /** @return list<DiscoveryOperationEvent> */
    public function all(): array;

    /** @return list<DiscoveryOperationEvent> */
    public function latest(int $limit = 25): array;

    public function clear(): void;
}
