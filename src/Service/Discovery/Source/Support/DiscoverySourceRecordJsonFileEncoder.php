<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source\Support;

use App\Dto\Discovery\DiscoverySourceRecord;

final class DiscoverySourceRecordJsonFileEncoder
{
    /**
     * @param list<DiscoverySourceRecord> $records
     */
    public function encodeRecords(array $records): string
    {
        $payload = array_map(
            static fn (DiscoverySourceRecord $record): array => [
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
