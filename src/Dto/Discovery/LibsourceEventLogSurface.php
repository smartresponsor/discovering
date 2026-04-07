<?php

declare(strict_types=1);

namespace App\Dto\Discovery;


/**
 * Represents the libsource event log surface contract used by discovery application, management, or state coordination flows.
 */
final class LibsourceEventLogSurface
{
    /**
     * @param list<LibsourceOperatorEvent> $events
     * @param list<string> $availableLevels
     * @param array<string, string> $availablePresets
     */
    public function __construct(
        public string $backendClass,
        public int $totalEvents,
        public int $filteredTotalEvents,
        public array $events,
        public array $availableLevels,
        public array $availablePresets,
        public ?string $activePreset,
        public ?string $activeLevel,
        public ?string $activeSearch,
        public int $page,
        public int $perPage,
        public int $totalPages,
    ) {
    }
}
