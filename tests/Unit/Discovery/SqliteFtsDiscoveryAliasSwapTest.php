<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Adapter\SqliteFtsDiscoveryAdapter;
use App\Tests\Support\DiscoveryTempFilesystemTestCase;

final class SqliteFtsDiscoveryAliasSwapTest extends DiscoveryTempFilesystemTestCase
{
    public function testItServesSearchesFromSwappedAliasTarget(): void
    {
        $path = $this->createTempSqlitePath('discovering-alias-');

        $adapter = new SqliteFtsDiscoveryAdapter($path);
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
