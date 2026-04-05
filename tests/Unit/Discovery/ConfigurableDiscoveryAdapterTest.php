<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Adapter\ConfigurableDiscoveryAdapter;
use App\Service\Discovery\Adapter\MeiliDiscoveryAdapter;
use App\Service\Discovery\Adapter\SqliteFtsDiscoveryAdapter;
use PHPUnit\Framework\TestCase;

final class ConfigurableDiscoveryAdapterTest extends TestCase
{
    public function testFallsBackToSqliteWhenMeiliBackendIsNotConfigured(): void
    {
        $adapter = new ConfigurableDiscoveryAdapter(
            backend: 'meili',
            meiliBase: '',
            sqliteAdapter: new SqliteFtsDiscoveryAdapter('/tmp/discovering-test.sqlite'),
            meiliAdapter: new MeiliDiscoveryAdapter(),
        );

        self::assertSame('sqlite-fts5', $adapter->getBackendName());
        self::assertTrue($adapter->supportsStagedRebuild());
    }

    public function testUsesMeiliWhenBackendAndBaseUrlAreConfigured(): void
    {
        $adapter = new ConfigurableDiscoveryAdapter(
            backend: 'meili',
            meiliBase: 'http://meili.internal:7700',
            sqliteAdapter: new SqliteFtsDiscoveryAdapter('/tmp/discovering-test.sqlite'),
            meiliAdapter: new MeiliDiscoveryAdapter('http://meili.internal:7700', 'secret', 'discovering_prod'),
        );

        self::assertSame('meilisearch', $adapter->getBackendName());
        self::assertFalse($adapter->supportsStagedRebuild());
    }
}
