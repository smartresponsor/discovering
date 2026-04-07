<?php
declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoveryHit;
use App\Service\Discovery\DiscoveryFeedbackStore;
use App\Service\Discovery\DiscoveryLearningService;
use App\Tests\Support\DiscoveryTempFilesystemTestCase;


/**
 * Exercises the discovery learning service test case for the Discovering component.
 */
final class DiscoveryLearningServiceTest extends DiscoveryTempFilesystemTestCase
{
    public function testItCalculatesFeedbackBoostFromPersistedClicks(): void
    {
        $path = $this->createTempSqlitePath('discovering-learning-');
        $service = new DiscoveryLearningService(new DiscoveryFeedbackStore($path));

        $service->recordUsefulClick('playbook', 'playbook-1', 'Playbook', 'ref');
        $service->recordUsefulClick('playbook', 'playbook-1', 'Playbook', 'ref');
        $service->recordUsefulClick('playbook', 'playbook-1', 'Playbook', 'ref');

        $hit = new DiscoveryHit(id: 'playbook-1', title: 'Playbook', resource: 'playbook');

        self::assertSame(3, $service->getFeedbackCount($hit));
        self::assertGreaterThan(0.0, $service->calculateFeedbackBoost(3));

    }
}
