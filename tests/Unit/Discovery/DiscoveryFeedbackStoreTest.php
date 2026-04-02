<?php
declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\DiscoveryFeedbackStore;
use PHPUnit\Framework\TestCase;

final class DiscoveryFeedbackStoreTest extends TestCase
{
    public function testItPersistsAndIncrementsFeedbackCounts(): void
    {
        $path = sys_get_temp_dir() . '/discovering-feedback-' . uniqid('', true) . '.sqlite';
        $store = new DiscoveryFeedbackStore($path);

        self::assertSame(1, $store->recordClick('briefing', 'briefing-1', 'Title', 'reference'));
        self::assertSame(2, $store->recordClick('briefing', 'briefing-1', 'Title', 'reference'));
        self::assertSame(2, $store->getClickCount('briefing', 'briefing-1'));

        @unlink($path);
    }
}
