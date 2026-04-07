<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoveryOperationEvent;
use App\Service\Discovery\Operations\ConfigurableDiscoveryOperationEventLogStore;
use App\Service\Discovery\Operations\DiscoveryOperationEventJsonSerializer;
use App\Service\Discovery\Operations\FileDiscoveryOperationEventLogStore;
use App\Service\Discovery\Operations\PdoDiscoveryOperationEventLogStore;
use App\Tests\Support\DiscoveryTempFilesystemTestCase;


/**
 * Exercises the configurable discovery operation event log store test case for the Discovering component.
 */
final class ConfigurableDiscoveryOperationEventLogStoreTest extends DiscoveryTempFilesystemTestCase
{
    public function testSelectsFileBackendWhenConfigured(): void
    {
        $path = $this->createTempJsonPath('discovering-operation-log-');
        $store = new ConfigurableDiscoveryOperationEventLogStore(
            new FileDiscoveryOperationEventLogStore($path, new DiscoveryOperationEventJsonSerializer()),
            new PdoDiscoveryOperationEventLogStore('', null, null, 'discovery_operation_event_log'),
            backend: 'file',
            pdoDsn: '',
        );

        $store->append(new DiscoveryOperationEvent('req-1', 'http', 'discovery.query', 'ok', '2026-04-03T18:00:00+00:00', []));

        self::assertFileExists($path);
        self::assertCount(1, $store->all());
    }

    public function testRejectsUnknownBackend(): void
    {
        $path = $this->createTempJsonPath('discovering-operation-log-');
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
