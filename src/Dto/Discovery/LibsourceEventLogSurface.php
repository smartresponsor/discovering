<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

final class LibsourceEventLogSurface
{
    /**
     * @param list<LibsourceOperatorEvent> $events
     * @param list<string> $availableLevels
     */
    public function __construct(
        public string $backendClass,
        public int $totalEvents,
        public int $filteredTotalEvents,
        public array $events,
        public array $availableLevels,
        public ?string $activeLevel,
        public ?string $activeSearch,
        public int $page,
        public int $perPage,
        public int $totalPages,
    ) {
    }
}
