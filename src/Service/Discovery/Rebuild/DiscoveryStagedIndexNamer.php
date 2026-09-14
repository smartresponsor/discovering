<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Rebuild;

/**
 * Provides the discovery staged index namer capability within the discovery component.
 */
final class DiscoveryStagedIndexNamer
{
    /**
     * Performs the for logical index operation for this discovery service.
     */
    public function forLogicalIndex(string $logicalIndex, string $evidenceId): string
    {
        $suffix = preg_replace('/[^a-z0-9]+/i', '_', strtolower($evidenceId)) ?? 'reb';
        $suffix = trim($suffix, '_');
        if ('' === $suffix) {
            $suffix = 'reb';
        }

        return sprintf('%s__staged__%s', $logicalIndex, $suffix);
    }
}
