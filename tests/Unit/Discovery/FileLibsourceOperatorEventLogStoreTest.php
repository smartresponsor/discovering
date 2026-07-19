<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Dto\Discovery\LibsourceOperatorEvent;
use App\Discovering\Service\Discovery\Libsource\Log\FileLibsourceOperatorEventLogStore;
use App\Discovering\Service\Discovery\Libsource\Log\LibsourceOperatorEventJsonSerializer;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;

/**
 * Exercises the file libsource operator event log store test case for the Discovering component.
 */
final class FileLibsourceOperatorEventLogStoreTest extends DiscoveryTempFilesystemTestCase
{
    public function testItPersistsAndClearsEventsInJsonFile(): void
    {
        $path = $this->createTempFilePath('discovering-libsource-log-', '.json');
        $store = new FileLibsourceOperatorEventLogStore($path, new LibsourceOperatorEventJsonSerializer());

        $store->append(new LibsourceOperatorEvent('action:inspect', 'info', 'Inspected source.', ['sourceName' => 'project-source-provider']));
        $store->append(new LibsourceOperatorEvent('action:audit-alignment', 'info', 'Audited alignment.'));

        $events = $store->all();

        self::assertCount(2, $events);
        self::assertSame('action:inspect', $events[0]->eventName);
        self::assertSame('project-source-provider', $events[0]->context['sourceName']);

        $store->clear();

        self::assertSame([], $store->all());

        @unlink($path);
    }
}
