<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\LibsourceOperatorEvent;
use App\Service\Discovery\Libsource\Log\ConfigurableLibsourceOperatorEventLogStore;
use App\Service\Discovery\Libsource\Log\DoctrineLibsourceOperatorEventLogStore;
use App\Service\Discovery\Libsource\Log\FileLibsourceOperatorEventLogStore;
use App\Service\Discovery\Libsource\Log\LibsourceOperatorEventJsonSerializer;
use App\Tests\Support\DiscoveryDoctrineEntityManagerFactory;
use App\Tests\Support\DiscoveryTempFilesystemTestCase;

/**
 * Exercises the configurable libsource operator event log store test case for the Discovering component.
 */
final class ConfigurableLibsourceOperatorEventLogStoreTest extends DiscoveryTempFilesystemTestCase
{
    public function testSelectsFileBackendWhenConfigured(): void
    {
        $path = $this->createTempFilePath('discovering-libsource-log-', '.json');
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $store = new ConfigurableLibsourceOperatorEventLogStore(
            new FileLibsourceOperatorEventLogStore($path, new LibsourceOperatorEventJsonSerializer()),
            new DoctrineLibsourceOperatorEventLogStore($entityManager),
            backend: 'file',
        );

        $store->append(new LibsourceOperatorEvent('sync.completed', 'info', 'Sync completed.', []));

        self::assertFileExists($path);
        self::assertCount(1, $store->all());
        unlink($path);
    }

    public function testRejectsUnknownBackend(): void
    {
        $path = $this->createTempFilePath('discovering-libsource-log-', '.json');
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $store = new ConfigurableLibsourceOperatorEventLogStore(
            new FileLibsourceOperatorEventLogStore($path, new LibsourceOperatorEventJsonSerializer()),
            new DoctrineLibsourceOperatorEventLogStore($entityManager),
            backend: 'redis',
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unsupported libsource operator event log backend');
        $store->all();
    }
}
