<?php

declare(strict_types=1);

namespace App\Service\Discovery\Document;

use App\Dto\Discovery\DiscoverySourceRecord;
use App\ValueObject\Discovery\DiscoveryDocument;

final class DiscoveryDocumentFactory
{
    public function fromSourceRecord(DiscoverySourceRecord $record): DiscoveryDocument
    {
        return new DiscoveryDocument(
            resourceType: $record->resourceType,
            resourceId: $record->resourceId,
            title: $record->title,
            body: $record->body,
            filters: $record->filters,
            metadata: $record->metadata,
        );
    }
}
