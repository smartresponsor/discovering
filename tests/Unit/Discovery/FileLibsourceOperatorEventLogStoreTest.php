<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\LibsourceOperatorEvent;
use App\Service\Discovery\Libsource\Log\FileLibsourceOperatorEventLogStore;
use App\Service\Discovery\Libsource\Log\LibsourceOperatorEventJsonSerializer;
use PHPUnit\Framework\TestCase;

final class FileLibsourceOperatorEventLogStoreTest extends TestCase
{
    public function testItPersistsAndClearsEventsInJsonFile(): void
    {
        $path = sys_get_temp_dir() . '/discovering-libsource-log-' . uniqid('', true) . '.json';
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
