<?php

declare(strict_types=1);

namespace App\Discovering\ServiceInterface\Discovery;

/**
 * Defines the contract for the discovery feedback store capability within the discovery component.
 */
interface DiscoveryFeedbackStoreInterface
{
    /**
     * Performs the record click operation defined by this discovery contract.
     */
    public function recordClick(string $resource, string $hitId, string $title = '', string $reference = ''): int;

    public function getClickCount(string $resource, string $hitId): int;
}
