<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Adapter\SqliteFtsDiscoveryAdapter;
use PHPUnit\Framework\TestCase;

final class SqliteFtsDiscoveryAliasSwapTest extends TestCase
{
    public function testItServesSearchesFromSwappedAliasTarget(): void
    {
        $path = sys_get_temp_dir() . '/discovering-alias-' . bin2hex(random_bytes(4)) . '.sqlite';
        if (is_file($path)) {
            unlink($path);
        }

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

        if (is_file($path)) {
            unlink($path);
        }
    }
}
