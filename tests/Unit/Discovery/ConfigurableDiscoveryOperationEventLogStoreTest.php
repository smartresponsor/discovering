<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Dto\Discovery\DiscoveryOperationEvent;
use App\Discovering\Service\Discovery\Operations\ConfigurableDiscoveryOperationEventLogStore;
use App\Discovering\Service\Discovery\Operations\DiscoveryOperationEventJsonSerializer;
use App\Discovering\Service\Discovery\Operations\DoctrineDiscoveryOperationEventLogStore;
use App\Discovering\Service\Discovery\Operations\FileDiscoveryOperationEventLogStore;
use App\Discovering\Tests\Support\DiscoveryDoctrineEntityManagerFactory;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;

/**
 * Exercises the configurable discovery operation event log store test case for the Discovering component.
 */
final class ConfigurableDiscoveryOperationEventLogStoreTest extends DiscoveryTempFilesystemTestCase
{
    public function testSelectsFileBackendWhenConfigured(): void
    {
        $path = $this->createTempJsonPath('discovering-operation-log-');
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $store = new ConfigurableDiscoveryOperationEventLogStore(
            new FileDiscoveryOperationEventLogStore($path, new DiscoveryOperationEventJsonSerializer()),
            new DoctrineDiscoveryOperationEventLogStore($entityManager),
            backend: 'file',
        );

        $store->append(new DiscoveryOperationEvent('req-1', 'http', 'discovery.query', 'ok', '2026-04-03T18:00:00+00:00', []));

        self::assertFileExists($path);
        self::assertCount(1, $store->all());
    }

    public function testRejectsUnknownBackend(): void
    {
        $path = $this->createTempJsonPath('discovering-operation-log-');
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $store = new ConfigurableDiscoveryOperationEventLogStore(
            new FileDiscoveryOperationEventLogStore($path, new DiscoveryOperationEventJsonSerializer()),
            new DoctrineDiscoveryOperationEventLogStore($entityManager),
            backend: 'redis',
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unsupported discovery operation log backend');
        $store->all();
    }
}
