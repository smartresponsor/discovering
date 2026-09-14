<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Service\Discovery\Backend\SqliteFtsDiscoveryBackend;
use App\Discovering\Tests\Support\DiscoveryDoctrineEntityManagerFactory;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;

/**
 * Exercises the sqlite fts discovery alias swap test case for the Discovering component.
 */
final class SqliteFtsDiscoveryBackendAliasSwapTest extends DiscoveryTempFilesystemTestCase
{
    public function testItServesSearchesFromSwappedAliasTarget(): void
    {
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $adapter = new SqliteFtsDiscoveryBackend($entityManager);
        $adapter->upsert('global', 'legacy', [
            'title' => 'Legacy document',
            'resource' => 'global',
            'reference' => 'legacy',
            'status' => 'active',
            'content' => 'legacy content',
        ]);
        $adapter->upsert('global__staged__candidate', 'staged', [
            'title' => 'Staged document',
            'resource' => 'global',
            'reference' => 'staged',
            'status' => 'active',
            'content' => 'staged content',
        ]);

        $adapter->swapAlias('global', 'global__staged__candidate');

        $rows = $adapter->search('global', 'staged');

        self::assertCount(1, $rows);
        self::assertSame('staged', $rows[0]['id']);
        self::assertSame('Staged document', $rows[0]['title']);
    }
}
