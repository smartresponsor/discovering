<?php

declare(strict_types=1);

namespace App\Discovering\Service;

use App\Discovering\Repository\Feedback\DiscoveryDoctrineFeedbackStore;
use App\Discovering\ServiceInterface\DiscoveryFeedbackStoreInterface; /**
 * Provides the configurable discovery feedback store capability within the discovery component.
 */
final class DiscoveryConfigurableFeedbackStore implements DiscoveryFeedbackStoreInterface
{
    public function __construct(
        private readonly DiscoveryDoctrineFeedbackStore $doctrineStore,
    ) {
    }

    /**
     * Records the click signal for discovery state and analytics flows.
     */
    public function recordClick(string $resource, string $hitId, string $title = '', string $reference = ''): int
    {
        return $this->doctrineStore->recordClick($resource, $hitId, $title, $reference);
    }

    /**
     * Returns the click count value exposed by this service.
     */
    public function getClickCount(string $resource, string $hitId): int
    {
        return $this->doctrineStore->getClickCount($resource, $hitId);
    }
}
