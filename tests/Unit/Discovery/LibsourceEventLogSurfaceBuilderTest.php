<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\LibsourceEventLogQuery;
use App\Dto\Discovery\LibsourceOperatorEvent;
use App\Service\Discovery\Libsource\LibsourceEventLogSurfaceBuilder;
use App\Service\Discovery\Libsource\Log\EphemeralLibsourceOperatorEventLogStore;
use PHPUnit\Framework\TestCase;

final class LibsourceEventLogSurfaceBuilderTest extends TestCase
{
    public function testItBuildsNewestFirstSurfaceFromStoredEvents(): void
    {
        $store = new EphemeralLibsourceOperatorEventLogStore();
        $store->append(new LibsourceOperatorEvent('action:first', 'info', 'First action'));
        $store->append(new LibsourceOperatorEvent('action:second', 'warning', 'Second action'));

        $surface = (new LibsourceEventLogSurfaceBuilder($store))->build();

        self::assertSame(2, $surface->totalEvents);
        self::assertSame(2, $surface->filteredTotalEvents);
        self::assertSame('Second action', $surface->events[0]->summary);
        self::assertSame('First action', $surface->events[1]->summary);
    }

    public function testItFiltersByLevelAndSearch(): void
    {
        $store = new EphemeralLibsourceOperatorEventLogStore();
        $store->append(new LibsourceOperatorEvent('action:inspect', 'info', 'Inspect project provider', ['source' => 'project-source-provider']));
        $store->append(new LibsourceOperatorEvent('action:clear', 'warning', 'Clear event log'));
        $store->append(new LibsourceOperatorEvent('action:inspect', 'info', 'Inspect document provider', ['source' => 'document-source-provider']));

        $surface = (new LibsourceEventLogSurfaceBuilder($store))->build(new LibsourceEventLogQuery(
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
        $store = new EphemeralLibsourceOperatorEventLogStore();

        foreach (range(1, 12) as $index) {
            $store->append(new LibsourceOperatorEvent(
                eventName: 'action:test',
                level: 'info',
                summary: sprintf('Event %02d', $index),
            ));
        }

        $surface = (new LibsourceEventLogSurfaceBuilder($store))->build(new LibsourceEventLogQuery(
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
}
