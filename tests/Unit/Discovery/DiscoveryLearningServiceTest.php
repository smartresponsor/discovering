<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\DTO\DiscoveryHitDTO;
use App\Discovering\Repository\Feedback\DiscoveryDoctrineFeedbackStore;
use App\Discovering\Service\DiscoveryLearningService;
use App\Discovering\Tests\Support\DiscoveryDoctrineEntityManagerFactory;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;

/**
 * Exercises the discovery learning service test case for the Discovering component.
 */
final class DiscoveryLearningServiceTest extends DiscoveryTempFilesystemTestCase
{
    public function testItCalculatesFeedbackBoostFromPersistedClicks(): void
    {
        $entityManager = DiscoveryDoctrineEntityManagerFactory::create();
        $service = new DiscoveryLearningService(new DiscoveryDoctrineFeedbackStore($entityManager));

        $service->recordUsefulClick('playbook', 'playbook-1', 'Playbook', 'ref');
        $service->recordUsefulClick('playbook', 'playbook-1', 'Playbook', 'ref');
        $service->recordUsefulClick('playbook', 'playbook-1', 'Playbook', 'ref');

        $hit = new DiscoveryHitDTO(id: 'playbook-1', title: 'Playbook', resource: 'playbook');

        self::assertSame(3, $service->getFeedbackCount($hit));
        self::assertGreaterThan(0.0, $service->calculateFeedbackBoost(3));
    }
}
