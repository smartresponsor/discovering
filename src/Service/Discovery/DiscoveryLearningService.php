<?php

declare(strict_types=1);

namespace App\Service\Discovery;

use App\Dto\Discovery\DiscoveryHit;

final class DiscoveryLearningService
{
    public function __construct(
        private readonly DiscoveryFeedbackStoreInterface $feedbackStore,
    ) {
    }

    public function recordUsefulClick(string $resource, string $hitId, string $title = '', string $reference = ''): int
    {
        return $this->feedbackStore->recordClick($resource, $hitId, $title, $reference);
    }

    public function getFeedbackCount(DiscoveryHit $hit): int
    {
        return $this->feedbackStore->getClickCount($hit->resource, $hit->id);
    }

    public function calculateFeedbackBoost(int $feedbackCount): float
    {
        if ($feedbackCount <= 0) {
            return 0.0;
        }

        return round(log($feedbackCount + 1, 2) * 3.0, 2);
    }
}
