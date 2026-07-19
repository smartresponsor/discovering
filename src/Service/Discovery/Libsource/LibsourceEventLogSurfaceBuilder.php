<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Libsource;

use App\Discovering\Dto\Discovery\LibsourceEventLogQuery;
use App\Discovering\Dto\Discovery\LibsourceEventLogSurface;
use App\Discovering\Dto\Discovery\LibsourceOperatorEvent;
use App\Discovering\ServiceInterface\Discovery\Libsource\Log\LibsourceOperatorEventLogStoreInterface;

/**
 * Builds the libsource event log surface output used by discovery management or diagnostics flows.
 */
final class LibsourceEventLogSurfaceBuilder
{
    /**
     * @var array<string, string>
     */
    private const PRESETS = [
        'warnings' => 'Warnings only',
        'inspections' => 'Inspect actions',
        'maintenance' => 'Maintenance actions',
        'audits' => 'Audit actions',
    ];

    public function __construct(
        private readonly LibsourceOperatorEventLogStoreInterface $eventLogStore,
    ) {
    }

    /**
     * Builds the build result for this discovery workflow.
     */
    public function build(?LibsourceEventLogQuery $query = null): LibsourceEventLogSurface
    {
        $query ??= new LibsourceEventLogQuery();

        $storedEvents = array_values(array_reverse($this->eventLogStore->all()));
        $availableLevels = $this->buildAvailableLevels($storedEvents);
        $filteredEvents = array_values(array_filter(
            $storedEvents,
            fn (LibsourceOperatorEvent $event): bool => $this->matches($event, $query),
        ));

        $perPage = max(1, min(100, $query->perPage));
        $filteredTotalEvents = count($filteredEvents);
        $totalPages = max(1, (int) ceil($filteredTotalEvents / $perPage));
        $page = max(1, min($query->page, $totalPages));
        $offset = ($page - 1) * $perPage;
        $pagedEvents = array_slice($filteredEvents, $offset, $perPage);

        return new LibsourceEventLogSurface(
            backendClass: $this->eventLogStore::class,
            totalEvents: count($storedEvents),
            filteredTotalEvents: $filteredTotalEvents,
            events: $pagedEvents,
            availableLevels: $availableLevels,
            availablePresets: self::PRESETS,
            activePreset: $query->preset,
            activeLevel: $query->level,
            activeSearch: $query->search,
            page: $page,
            perPage: $perPage,
            totalPages: $totalPages,
        );
    }

    /**
     * @param list<LibsourceOperatorEvent> $events
     *
     * @return list<string>
     */
    private function buildAvailableLevels(array $events): array
    {
        $levels = [];

        foreach ($events as $event) {
            $levels[$event->level] = true;
        }

        $availableLevels = array_keys($levels);
        sort($availableLevels);

        return $availableLevels;
    }

    private function matches(LibsourceOperatorEvent $event, LibsourceEventLogQuery $query): bool
    {
        if (!$this->matchesPreset($event, $query->preset)) {
            return false;
        }

        if (null !== $query->level && '' !== $query->level && $event->level !== $query->level) {
            return false;
        }

        if (null === $query->search || '' === $query->search) {
            return true;
        }

        $needle = mb_strtolower($query->search);
        $haystack = mb_strtolower(implode(' ', [
            $event->eventName,
            $event->summary,
            json_encode($event->context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '',
        ]));

        return str_contains($haystack, $needle);
    }

    private function matchesPreset(LibsourceOperatorEvent $event, ?string $preset): bool
    {
        if (null === $preset || '' === $preset) {
            return true;
        }

        return match ($preset) {
            'warnings' => 'warning' === $event->level,
            'inspections' => 'action:inspect' === $event->eventName,
            'maintenance' => in_array($event->eventName, ['action:rebuild-coverage', 'action:clear-event-log'], true),
            'audits' => 'action:audit-alignment' === $event->eventName,
            default => true,
        };
    }
}
