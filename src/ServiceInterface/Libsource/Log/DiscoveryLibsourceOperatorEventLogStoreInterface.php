<?php

declare(strict_types=1);

namespace App\Discovering\ServiceInterface\Libsource\Log;

use App\Discovering\DTO\DiscoveryLibsourceOperatorEventDTO;

/**
 * Defines the contract for the libsource operator event log store capability within the discovery component.
 */
interface DiscoveryLibsourceOperatorEventLogStoreInterface
{
    /**
     * Performs the append operation defined by this discovery contract.
     */
    public function append(DiscoveryLibsourceOperatorEventDTO $event): void;

    /**
     * @return list<DiscoveryLibsourceOperatorEventDTO>
     */
    public function all(): array;

    public function clear(): void;
}
