<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\ConfigurableDiscoveryFeedbackStore;
use App\Service\Discovery\DoctrineDiscoveryFeedbackStore;
use App\Tests\Support\DiscoveryDoctrineEntityManagerFactory;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the configurable discovery feedback store test case for the Discovering component.
 */
final class ConfigurableDiscoveryFeedbackStoreTest extends TestCase
{
    public function testItUsesDoctrineStore(): void
    {
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $store = new ConfigurableDiscoveryFeedbackStore(
            doctrineStore: new DoctrineDiscoveryFeedbackStore($entityManager),
        );

        self::assertSame(1, $store->recordClick('briefing', 'briefing-2', 'Title', 'reference'));
        self::assertSame(1, $store->getClickCount('briefing', 'briefing-2'));
        self::assertSame(0, $store->getClickCount('briefing', 'missing-hit'));
    }
}
