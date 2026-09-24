<?php

declare(strict_types=1);

namespace App\Discovering\Service\Libsource\Log;

use App\Discovering\DTO\DiscoveryLibsourceOperatorEventDTO;
use App\Discovering\ServiceInterface\Libsource\Log\DiscoveryLibsourceOperatorEventLogStoreInterface;

/**
 * Provides the ephemeral libsource operator event log store capability within the discovery component.
 */
final class DiscoveryEphemeralLibsourceOperatorEventLogStore implements DiscoveryLibsourceOperatorEventLogStoreInterface
{
    /**
     * @var list<DiscoveryLibsourceOperatorEventDTO>
     */
    private array $events = [];

    public function append(DiscoveryLibsourceOperatorEventDTO $event): void
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
