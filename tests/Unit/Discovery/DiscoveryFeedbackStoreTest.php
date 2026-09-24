<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Repository\Feedback\DiscoveryDoctrineFeedbackStore;
use App\Discovering\Tests\Support\DiscoveryDoctrineEntityManagerFactory;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;

/**
 * Exercises the discovery feedback store test case for the Discovering component.
 */
final class DiscoveryFeedbackStoreTest extends DiscoveryTempFilesystemTestCase
{
    public function testItPersistsAndIncrementsFeedbackCounts(): void
    {
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $store = new DiscoveryDoctrineFeedbackStore($entityManager);

        self::assertSame(1, $store->recordClick('briefing', 'briefing-1', 'Title', 'reference'));
        self::assertSame(2, $store->recordClick('briefing', 'briefing-1', 'Title', 'reference'));
        self::assertSame(2, $store->getClickCount('briefing', 'briefing-1'));
    }
}
