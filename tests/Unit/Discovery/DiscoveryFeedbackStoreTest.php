<?php
declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\DiscoveryFeedbackStore;
use App\Tests\Support\DiscoveryTempFilesystemTestCase;


/**
 * Exercises the discovery feedback store test case for the Discovering component.
 */
final class DiscoveryFeedbackStoreTest extends DiscoveryTempFilesystemTestCase
{
    public function testItPersistsAndIncrementsFeedbackCounts(): void
    {
        $path = $this->createTempSqlitePath('discovering-feedback-');
        $store = new DiscoveryFeedbackStore($path);

        self::assertSame(1, $store->recordClick('briefing', 'briefing-1', 'Title', 'reference'));
        self::assertSame(2, $store->recordClick('briefing', 'briefing-1', 'Title', 'reference'));
        self::assertSame(2, $store->getClickCount('briefing', 'briefing-1'));

    }
}
