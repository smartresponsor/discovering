<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Service\Discovery\Adapter\ConfigurableDiscoveryAdapter;
use App\Discovering\Service\Discovery\Adapter\MeiliDiscoveryAdapter;
use App\Discovering\Service\Discovery\Adapter\SqliteFtsDiscoveryAdapter;
use App\Discovering\Tests\Support\DiscoveryDoctrineEntityManagerFactory;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the configurable discovery adapter test case for the Discovering component.
 */
final class ConfigurableDiscoveryAdapterTest extends TestCase
{
    public function testFallsBackToSqliteWhenMeiliBackendIsNotConfigured(): void
    {
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $adapter = new ConfigurableDiscoveryAdapter(
            backend: 'meili',
            meiliBase: '',
            sqliteAdapter: new SqliteFtsDiscoveryAdapter($entityManager),
            meiliAdapter: new MeiliDiscoveryAdapter(),
        );

        self::assertSame('sqlite-fts5', $adapter->getBackendName());
        self::assertTrue($adapter->supportsStagedRebuild());
    }

    public function testUsesMeiliWhenBackendAndBaseUrlAreConfigured(): void
    {
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $adapter = new ConfigurableDiscoveryAdapter(
            backend: 'meili',
            meiliBase: 'http://meili.internal:7700',
            sqliteAdapter: new SqliteFtsDiscoveryAdapter($entityManager),
            meiliAdapter: new MeiliDiscoveryAdapter('http://meili.internal:7700', 'secret', 'discovering_prod'),
        );

        self::assertSame('meilisearch', $adapter->getBackendName());
        self::assertFalse($adapter->supportsStagedRebuild());
    }
}
