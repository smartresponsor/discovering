<?php

declare(strict_types=1);

namespace App\Discovering\ServiceInterface\Operations;

use App\Discovering\DTO\DiscoveryOperationEventDTO;

/**
 * Defines the contract for the discovery operation event log store capability within the discovery component.
 */
interface DiscoveryOperationEventLogStoreInterface
{
    /**
     * Performs the append operation defined by this discovery contract.
     */
    public function append(DiscoveryOperationEventDTO $event): void;

    /** @return list<DiscoveryOperationEventDTO> */
    public function all(): array;

    /** @return list<DiscoveryOperationEventDTO> */
    public function latest(int $limit = 25): array;

    public function clear(): void;
}
