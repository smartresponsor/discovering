<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\PdoDiscoveryFeedbackStore;
use PHPUnit\Framework\TestCase;

final class PdoDiscoveryFeedbackStoreTest extends TestCase
{
    public function testItPersistsAndIncrementsFeedbackCountsInPdoStore(): void
    {
        $store = new PdoDiscoveryFeedbackStore(
            dsn: 'sqlite::memory:',
            user: null,
            password: null,
            tableName: 'discovery_feedback_test',
        );

        self::assertSame(1, $store->recordClick('briefing', 'briefing-1', 'Title', 'reference'));
        self::assertSame(2, $store->recordClick('briefing', 'briefing-1', 'Title', 'reference'));
        self::assertSame(2, $store->getClickCount('briefing', 'briefing-1'));
        self::assertSame(0, $store->getClickCount('briefing', 'missing-hit'));
    }
}
