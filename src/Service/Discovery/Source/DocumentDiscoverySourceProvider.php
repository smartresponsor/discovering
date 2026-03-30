<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source;

use App\Dto\Discovery\DiscoverySourceRecord;
use App\ServiceInterface\Discovery\Source\DiscoverySourceProviderInterface;

final class DocumentDiscoverySourceProvider implements DiscoverySourceProviderInterface
{
    public function getResourceType(): string
    {
        return 'document';
    }

    public function provide(): array
    {
        return [
            new DiscoverySourceRecord(
                resourceType: 'document',
                resourceId: 'document-discovery-product-manifest',
                title: 'Discovery product manifesto',
                body: 'Product-facing note explaining that discovery is a resource retrieval and indexing component rather than a generic search engine.',
                filters: ['status' => 'published', 'visibility' => 'internal'],
                metadata: ['tags' => ['manifest', 'product', 'discovery']],
            ),
            new DiscoverySourceRecord(
                resourceType: 'document',
                resourceId: 'document-backend-guide',
                title: 'Discovery backend guide',
                body: 'Implementation note comparing local SQLite-style discovery flow, scalable Meilisearch flow, and future backend portability expectations.',
                filters: ['status' => 'draft', 'visibility' => 'internal'],
                metadata: ['tags' => ['backend', 'sqlite', 'meilisearch']],
            ),
        ];
    }
}
