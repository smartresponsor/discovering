<?php

declare(strict_types=1);

namespace App\ServiceInterface\Discovery\Operations;

use App\Dto\Discovery\DiscoveryOperationEvent;

/**
 * Defines the contract for the discovery operation event log store capability within the discovery component.
 */
interface DiscoveryOperationEventLogStoreInterface
{
    /**
     * Performs the append operation defined by this discovery contract.
     */
    public function append(DiscoveryOperationEvent $event): void;

    /** @return list<DiscoveryOperationEvent> */
    public function all(): array;

    /** @return list<DiscoveryOperationEvent> */
    public function latest(int $limit = 25): array;

    public function clear(): void;
}
