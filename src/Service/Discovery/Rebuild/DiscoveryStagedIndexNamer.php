<?php

declare(strict_types=1);

namespace App\Service\Discovery\Rebuild;

final class DiscoveryStagedIndexNamer
{
    public function forLogicalIndex(string $logicalIndex, string $evidenceId): string
    {
        $suffix = preg_replace('/[^a-z0-9]+/i', '_', strtolower($evidenceId)) ?? 'reb';
        $suffix = trim($suffix, '_');
        if ($suffix === '') {
            $suffix = 'reb';
        }

        return sprintf('%s__staged__%s', $logicalIndex, $suffix);
    }
}
