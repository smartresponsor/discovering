<?php

declare(strict_types=1);

namespace App\Discovering\Repository\Source\Support;

use App\Discovering\DTO\DiscoverySourceRecordDTO;

/**
 * Handles discovery source record json file encoder concerns for discovery state, source, or API payloads.
 */
final class DiscoverySourceRecordJsonFileEncoder
{
    /**
     * @param list<DiscoverySourceRecordDTO> $records
     */
    public function encodeRecords(array $records): string
    {
        $payload = array_map(
            static fn (DiscoverySourceRecordDTO $record): array => [
                'resourceType' => $record->resourceType,
                'resourceId' => $record->resourceId,
                'title' => $record->title,
                'body' => $record->body,
                'filters' => $record->filters,
                'metadata' => $record->metadata,
            ],
            $records,
        );

        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '[]';
    }
}
