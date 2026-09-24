<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Repository\Feedback\DiscoveryDoctrineFeedbackStore;
use App\Discovering\Service\DiscoveryConfigurableFeedbackStore;
use App\Discovering\Tests\Support\DiscoveryDoctrineEntityManagerFactory;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the configurable discovery feedback store test case for the Discovering component.
 */
final class ConfigurableDiscoveryFeedbackStoreTest extends TestCase
{
    public function testItUsesDoctrineStore(): void
    {
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $store = new DiscoveryConfigurableFeedbackStore(
            doctrineStore: new DiscoveryDoctrineFeedbackStore($entityManager),
        );

        self::assertSame(1, $store->recordClick('briefing', 'briefing-2', 'Title', 'reference'));
        self::assertSame(1, $store->getClickCount('briefing', 'briefing-2'));
        self::assertSame(0, $store->getClickCount('briefing', 'missing-hit'));
    }
}
