<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Libsource\Log;

use App\Discovering\Dto\Discovery\LibsourceOperatorEvent;
use App\Discovering\ServiceInterface\Discovery\Libsource\Log\LibsourceOperatorEventLogStoreInterface;

/**
 * Provides the ephemeral libsource operator event log store capability within the discovery component.
 */
final class EphemeralLibsourceOperatorEventLogStore implements LibsourceOperatorEventLogStoreInterface
{
    /**
     * @var list<LibsourceOperatorEvent>
     */
    private array $events = [];

    public function append(LibsourceOperatorEvent $event): void
    {
        $this->events[] = $event;
    }

    /**
     * Performs the all operation for this discovery service.
     */
    public function all(): array
    {
        return $this->events;
    }

    /**
     * Performs the clear operation for this discovery service.
     */
    public function clear(): void
    {
        $this->events = [];
    }
}
