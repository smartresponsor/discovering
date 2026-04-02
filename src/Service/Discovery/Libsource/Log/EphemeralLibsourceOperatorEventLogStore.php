<?php

declare(strict_types=1);

namespace App\Service\Discovery\Libsource\Log;

use App\Dto\Discovery\LibsourceOperatorEvent;

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

    public function all(): array
    {
        return $this->events;
    }

    public function clear(): void
    {
        $this->events = [];
    }
}
