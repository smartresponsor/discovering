<?php

declare(strict_types=1);

namespace App\ServiceInterface\Discovery\Libsource\Log;

use App\Dto\Discovery\LibsourceOperatorEvent;

/**
 * Defines the contract for the libsource operator event log store capability within the discovery component.
 */
interface LibsourceOperatorEventLogStoreInterface
{
    /**
     * Performs the append operation defined by this discovery contract.
     */
    public function append(LibsourceOperatorEvent $event): void;

    /**
     * @return list<LibsourceOperatorEvent>
     */
    public function all(): array;

    public function clear(): void;
}
