<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\DTO\DiscoveryOperationEventDTO;
use App\Discovering\Repository\Operations\DiscoveryDoctrineOperationEventLogStore;
use App\Discovering\Service\Operations\DiscoveryConfigurableOperationEventLogStore;
use App\Discovering\Service\Operations\DiscoveryFileOperationEventLogStore;
use App\Discovering\Service\Operations\DiscoveryOperationEventJsonSerializer;
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
        $store = new DiscoveryConfigurableOperationEventLogStore(
            new DiscoveryFileOperationEventLogStore($path, new DiscoveryOperationEventJsonSerializer()),
            new DiscoveryDoctrineOperationEventLogStore($entityManager),
            backend: 'file',
        );

        $store->append(new DiscoveryOperationEventDTO('req-1', 'http', 'discovery.query', 'ok', '2026-04-03T18:00:00+00:00', []));

        self::assertFileExists($path);
        self::assertCount(1, $store->all());
    }

    public function testRejectsUnknownBackend(): void
    {
        $path = $this->createTempJsonPath('discovering-operation-log-');
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $store = new DiscoveryConfigurableOperationEventLogStore(
            new DiscoveryFileOperationEventLogStore($path, new DiscoveryOperationEventJsonSerializer()),
            new DiscoveryDoctrineOperationEventLogStore($entityManager),
            backend: 'redis',
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unsupported discovery operation log backend');
        $store->all();
    }
}
