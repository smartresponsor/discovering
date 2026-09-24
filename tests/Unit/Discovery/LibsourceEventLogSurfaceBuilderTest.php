<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Builder\Libsource\DiscoveryLibsourceEventLogSurfaceBuilder;
use App\Discovering\DTO\DiscoveryLibsourceEventLogQueryDTO;
use App\Discovering\DTO\DiscoveryLibsourceOperatorEventDTO;
use App\Discovering\Service\Libsource\Log\DiscoveryEphemeralLibsourceOperatorEventLogStore;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the libsource event log surface builder test case for the Discovering component.
 */
final class LibsourceEventLogSurfaceBuilderTest extends TestCase
{
    public function testItBuildsNewestFirstSurfaceFromStoredEvents(): void
    {
        $store = new DiscoveryEphemeralLibsourceOperatorEventLogStore();
        $store->append(new DiscoveryLibsourceOperatorEventDTO('action:first', 'info', 'First action'));
        $store->append(new DiscoveryLibsourceOperatorEventDTO('action:second', 'warning', 'Second action'));

        $surface = (new DiscoveryLibsourceEventLogSurfaceBuilder($store))->build();

        self::assertSame(2, $surface->totalEvents);
        self::assertSame(2, $surface->filteredTotalEvents);
        self::assertSame('Second action', $surface->events[0]->summary);
        self::assertSame('First action', $surface->events[1]->summary);
    }

    public function testItFiltersByLevelAndSearch(): void
    {
        $store = new DiscoveryEphemeralLibsourceOperatorEventLogStore();
        $store->append(new DiscoveryLibsourceOperatorEventDTO('action:inspect', 'info', 'Inspect project provider', ['source' => 'project-source-provider']));
        $store->append(new DiscoveryLibsourceOperatorEventDTO('action:clear', 'warning', 'Clear event log'));
        $store->append(new DiscoveryLibsourceOperatorEventDTO('action:inspect', 'info', 'Inspect document provider', ['source' => 'document-source-provider']));

        $surface = (new DiscoveryLibsourceEventLogSurfaceBuilder($store))->build(new DiscoveryLibsourceEventLogQueryDTO(
            search: 'project',
            level: 'info',
            page: 1,
            perPage: 10,
        ));

        self::assertSame(3, $surface->totalEvents);
        self::assertSame(1, $surface->filteredTotalEvents);
        self::assertSame('Inspect project provider', $surface->events[0]->summary);
        self::assertSame(['info', 'warning'], $surface->availableLevels);
    }

    public function testItPaginatesFilteredEvents(): void
    {
        $store = new DiscoveryEphemeralLibsourceOperatorEventLogStore();

        foreach (range(1, 12) as $index) {
            $store->append(new DiscoveryLibsourceOperatorEventDTO(
                eventName: 'action:test',
                level: 'info',
                summary: sprintf('Event %02d', $index),
            ));
        }

        $surface = (new DiscoveryLibsourceEventLogSurfaceBuilder($store))->build(new DiscoveryLibsourceEventLogQueryDTO(
            page: 2,
            perPage: 5,
        ));

        self::assertSame(12, $surface->totalEvents);
        self::assertSame(12, $surface->filteredTotalEvents);
        self::assertSame(2, $surface->page);
        self::assertSame(3, $surface->totalPages);
        self::assertCount(5, $surface->events);
        self::assertSame('Event 07', $surface->events[0]->summary);
        self::assertSame('Event 03', $surface->events[4]->summary);
    }

    public function testItAppliesQuickPresetScopes(): void
    {
        $store = new DiscoveryEphemeralLibsourceOperatorEventLogStore();
        $store->append(new DiscoveryLibsourceOperatorEventDTO('action:inspect', 'info', 'Inspect project provider'));
        $store->append(new DiscoveryLibsourceOperatorEventDTO('action:audit-alignment', 'info', 'Audit alignment'));
        $store->append(new DiscoveryLibsourceOperatorEventDTO('action:clear-event-log', 'warning', 'Clear event log'));
        $store->append(new DiscoveryLibsourceOperatorEventDTO('action:rebuild-coverage', 'info', 'Rebuild coverage snapshot'));

        $warningSurface = (new DiscoveryLibsourceEventLogSurfaceBuilder($store))->build(new DiscoveryLibsourceEventLogQueryDTO(
            preset: 'warnings',
            page: 1,
            perPage: 10,
        ));

        self::assertSame(1, $warningSurface->filteredTotalEvents);
        self::assertSame('Clear event log', $warningSurface->events[0]->summary);
        self::assertSame('warnings', $warningSurface->activePreset);

        $inspectionSurface = (new DiscoveryLibsourceEventLogSurfaceBuilder($store))->build(new DiscoveryLibsourceEventLogQueryDTO(
            preset: 'inspections',
            page: 1,
            perPage: 10,
        ));

        self::assertSame(1, $inspectionSurface->filteredTotalEvents);
        self::assertSame('Inspect project provider', $inspectionSurface->events[0]->summary);
        self::assertArrayHasKey('maintenance', $inspectionSurface->availablePresets);
    }
}
