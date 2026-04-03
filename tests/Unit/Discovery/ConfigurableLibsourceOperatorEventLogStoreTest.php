<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\LibsourceOperatorEvent;
use App\Service\Discovery\Libsource\Log\ConfigurableLibsourceOperatorEventLogStore;
use App\Service\Discovery\Libsource\Log\FileLibsourceOperatorEventLogStore;
use App\Service\Discovery\Libsource\Log\LibsourceOperatorEventJsonSerializer;
use App\Service\Discovery\Libsource\Log\PdoLibsourceOperatorEventLogStore;
use PHPUnit\Framework\TestCase;

final class ConfigurableLibsourceOperatorEventLogStoreTest extends TestCase
{
    public function testSelectsFileBackendWhenConfigured(): void
    {
        $path = sys_get_temp_dir() . '/discovering-libsource-log-' . bin2hex(random_bytes(6)) . '.json';
        $store = new ConfigurableLibsourceOperatorEventLogStore(
            new FileLibsourceOperatorEventLogStore($path, new LibsourceOperatorEventJsonSerializer()),
            new PdoLibsourceOperatorEventLogStore('', null, null, 'discovery_libsource_operator_event_log'),
            backend: 'file',
            pdoDsn: '',
        );

        $store->append(new LibsourceOperatorEvent('sync.completed', 'info', 'Sync completed.', []));

        self::assertFileExists($path);
        self::assertCount(1, $store->all());
        unlink($path);
    }

    public function testRejectsUnknownBackend(): void
    {
        $path = sys_get_temp_dir() . '/discovering-libsource-log-' . bin2hex(random_bytes(6)) . '.json';
        $store = new ConfigurableLibsourceOperatorEventLogStore(
            new FileLibsourceOperatorEventLogStore($path, new LibsourceOperatorEventJsonSerializer()),
            new PdoLibsourceOperatorEventLogStore('', null, null, 'discovery_libsource_operator_event_log'),
            backend: 'redis',
            pdoDsn: '',
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unsupported libsource operator event log backend');
        $store->all();
    }
}
