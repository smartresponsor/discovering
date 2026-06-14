<?php

declare(strict_types=1);

namespace App\Service\Discovery;

use App\Dto\Discovery\DiscoveryHit;
use App\ServiceInterface\Discovery\DiscoveryFeedbackStoreInterface;

/**
 * Provides the discovery learning capability within the discovery component.
 */
final class DiscoveryLearningService
{
    public function __construct(
        private readonly DiscoveryFeedbackStoreInterface $feedbackStore,
    ) {
    }

    /**
     * Records the useful click signal for discovery state and analytics flows.
     */
    public function recordUsefulClick(string $resource, string $hitId, string $title = '', string $reference = ''): int
    {
        return $this->feedbackStore->recordClick($resource, $hitId, $title, $reference);
    }

    /**
     * Returns the feedback count value exposed by this service.
     */
    public function getFeedbackCount(DiscoveryHit $hit): int
    {
        return $this->feedbackStore->getClickCount($hit->resource, $hit->id);
    }

    /**
     * Performs the calculate feedback boost operation for this discovery service.
     */
    public function calculateFeedbackBoost(int $feedbackCount): float
    {
        if ($feedbackCount <= 0) {
            return 0.0;
        }

        return round(log($feedbackCount + 1, 2) * 3.0, 2);
    }
}
