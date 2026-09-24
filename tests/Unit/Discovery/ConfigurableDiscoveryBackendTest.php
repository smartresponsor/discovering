<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Repository\Backend\DiscoverySqliteFtsBackend;
use App\Discovering\Service\Backend\DiscoveryConfigurableBackend;
use App\Discovering\Service\Backend\DiscoveryMeiliBackend;
use App\Discovering\Tests\Support\DiscoveryDoctrineEntityManagerFactory;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the configurable discovery adapter test case for the Discovering component.
 */
final class ConfigurableDiscoveryBackendTest extends TestCase
{
    public function testFallsBackToSqliteWhenMeiliBackendIsNotConfigured(): void
    {
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $adapter = new DiscoveryConfigurableBackend(
            backend: 'meili',
            meiliBase: '',
            sqliteBackend: new DiscoverySqliteFtsBackend($entityManager),
            meiliBackend: new DiscoveryMeiliBackend(),
        );

        self::assertSame('sqlite-fts5', $adapter->getBackendName());
        self::assertTrue($adapter->supportsStagedRebuild());
    }

    public function testUsesMeiliWhenBackendAndBaseUrlAreConfigured(): void
    {
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $adapter = new DiscoveryConfigurableBackend(
            backend: 'meili',
            meiliBase: 'http://meili.internal:7700',
            sqliteBackend: new DiscoverySqliteFtsBackend($entityManager),
            meiliBackend: new DiscoveryMeiliBackend('http://meili.internal:7700', 'secret', 'discovering_prod'),
        );

        self::assertSame('meilisearch', $adapter->getBackendName());
        self::assertFalse($adapter->supportsStagedRebuild());
    }
}
