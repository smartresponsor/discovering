<?php
declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Adapter\SqliteFtsDiscoveryAdapter;
use PHPUnit\Framework\TestCase;

final class SqliteFtsDiscoveryAdapterTest extends TestCase
{
    public function testItReturnsFtsScoreForNonEmptyQueries(): void
    {
        $path = sys_get_temp_dir() . '/discovering-sqlite-' . uniqid('', true) . '.sqlite';
        $adapter = new SqliteFtsDiscoveryAdapter($path);

        $adapter->upsert('global', 'briefing-1', [
            'title' => 'Search portability briefing',
            'resource' => 'briefing',
            'reference' => 'briefing-search-portability',
            'status' => 'active',
            'content' => 'Search portability governance context',
        ]);

        $adapter->upsert('global', 'playbook-1', [
            'title' => 'Reindex operations playbook',
            'resource' => 'playbook',
            'reference' => 'playbook-reindex-operations',
            'status' => 'active',
            'content' => 'Operational reindex validation guide',
        ]);

        $results = $adapter->search('global', 'search portability', 10, 0);

        self::assertNotEmpty($results);
        self::assertArrayHasKey('ftsScore', $results[0]);
        self::assertIsNumeric($results[0]['ftsScore']);

        @unlink($path);
    }
}
