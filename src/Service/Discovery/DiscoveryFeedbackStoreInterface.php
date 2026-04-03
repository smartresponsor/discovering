<?php

declare(strict_types=1);

namespace App\Service\Discovery;

interface DiscoveryFeedbackStoreInterface
{
    public function recordClick(string $resource, string $hitId, string $title = '', string $reference = ''): int;

    public function getClickCount(string $resource, string $hitId): int;
}
