<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoveryOperationEvent;
use App\Service\Discovery\Operations\ConfigurableDiscoveryOperationEventLogStore;
use App\Service\Discovery\Operations\DiscoveryOperationEventJsonSerializer;
use App\Service\Discovery\Operations\FileDiscoveryOperationEventLogStore;
use App\Service\Discovery\Operations\PdoDiscoveryOperationEventLogStore;
use PHPUnit\Framework\TestCase;

final class ConfigurableDiscoveryOperationEventLogStoreTest extends TestCase
{
    public function testSelectsFileBackendWhenConfigured(): void
    {
        $path = sys_get_temp_dir() . '/discovering-operation-log-' . bin2hex(random_bytes(6)) . '.json';
        $store = new ConfigurableDiscoveryOperationEventLogStore(
            new FileDiscoveryOperationEventLogStore($path, new DiscoveryOperationEventJsonSerializer()),
            new PdoDiscoveryOperationEventLogStore('', null, null, 'discovery_operation_event_log'),
            backend: 'file',
            pdoDsn: '',
        );

        $store->append(new DiscoveryOperationEvent('req-1', 'http', 'discovery.query', 'ok', '2026-04-03T18:00:00+00:00', []));

        self::assertFileExists($path);
        self::assertCount(1, $store->all());
        unlink($path);
    }

    public function testRejectsUnknownBackend(): void
    {
        $path = sys_get_temp_dir() . '/discovering-operation-log-' . bin2hex(random_bytes(6)) . '.json';
        $store = new ConfigurableDiscoveryOperationEventLogStore(
            new FileDiscoveryOperationEventLogStore($path, new DiscoveryOperationEventJsonSerializer()),
            new PdoDiscoveryOperationEventLogStore('', null, null, 'discovery_operation_event_log'),
            backend: 'redis',
            pdoDsn: '',
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unsupported discovery operation log backend');
        $store->all();
    }
}
