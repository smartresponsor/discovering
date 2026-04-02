<?php
declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Adapter\SqliteFtsDiscoveryAdapter;
use PHPUnit\Framework\TestCase;

final class SqliteFtsDiscoveryAdapterTest extends TestCase
{
    public function testItReturnsFtsScoreAndContentForNonEmptyQueries(): void
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

        $results = $adapter->search('global', 'search portability', 10, 0);

        self::assertNotEmpty($results);
        self::assertArrayHasKey('ftsScore', $results[0]);
        self::assertArrayHasKey('content', $results[0]);
        self::assertIsNumeric($results[0]['ftsScore']);
        self::assertSame('Search portability governance context', $results[0]['content']);

        @unlink($path);
    }
}
