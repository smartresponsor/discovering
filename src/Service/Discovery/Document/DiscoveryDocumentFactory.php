<?php

declare(strict_types=1);

namespace App\Service\Discovery\Document;

use App\Dto\Discovery\DiscoverySourceRecord;
use App\ValueObject\Discovery\DiscoveryDocument;

final class DiscoveryDocumentFactory
{
    public function createFromSourceRecord(DiscoverySourceRecord $record): DiscoveryDocument
    {
        $status = $record->filters['status'] ?? '';
        $reference = $record->metadata['reference'] ?? $record->resourceId;

        return new DiscoveryDocument(
            id: $record->resourceId,
            resource: $record->resourceType,
            title: $record->title,
            reference: is_string($reference) ? $reference : $record->resourceId,
            status: is_string($status) ? $status : '',
            content: $record->body,
            fields: [
                'resourceType' => $record->resourceType,
                'resourceId' => $record->resourceId,
                'body' => $record->body,
                'filters' => json_encode($record->filters, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'metadata' => json_encode($record->metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ],
        );
    }
}
