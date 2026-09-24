<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\DTO\DiscoveryLibsourceOperatorEventDTO;
use App\Discovering\Repository\Libsource\DiscoveryDoctrineLibsourceOperatorEventLogStore;
use App\Discovering\Service\Libsource\Log\DiscoveryConfigurableLibsourceOperatorEventLogStore;
use App\Discovering\Service\Libsource\Log\DiscoveryFileLibsourceOperatorEventLogStore;
use App\Discovering\Service\Libsource\Log\DiscoveryLibsourceOperatorEventJsonSerializer;
use App\Discovering\Tests\Support\DiscoveryDoctrineEntityManagerFactory;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;

/**
 * Exercises the configurable libsource operator event log store test case for the Discovering component.
 */
final class ConfigurableLibsourceOperatorEventLogStoreTest extends DiscoveryTempFilesystemTestCase
{
    public function testSelectsFileBackendWhenConfigured(): void
    {
        $path = $this->createTempFilePath('discovering-libsource-log-', '.json');
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $store = new DiscoveryConfigurableLibsourceOperatorEventLogStore(
            new DiscoveryFileLibsourceOperatorEventLogStore($path, new DiscoveryLibsourceOperatorEventJsonSerializer()),
            new DiscoveryDoctrineLibsourceOperatorEventLogStore($entityManager),
            backend: 'file',
        );

        $store->append(new DiscoveryLibsourceOperatorEventDTO('sync.completed', 'info', 'Sync completed.', []));

        self::assertFileExists($path);
        self::assertCount(1, $store->all());
        unlink($path);
    }

    public function testRejectsUnknownBackend(): void
    {
        $path = $this->createTempFilePath('discovering-libsource-log-', '.json');
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $store = new DiscoveryConfigurableLibsourceOperatorEventLogStore(
            new DiscoveryFileLibsourceOperatorEventLogStore($path, new DiscoveryLibsourceOperatorEventJsonSerializer()),
            new DiscoveryDoctrineLibsourceOperatorEventLogStore($entityManager),
            backend: 'redis',
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unsupported libsource operator event log backend');
        $store->all();
    }
}
