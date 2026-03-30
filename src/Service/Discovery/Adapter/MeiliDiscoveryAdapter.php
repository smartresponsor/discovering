<?php

declare(strict_types=1);

namespace App\Service\Discovery\Adapter;

use App\Dto\Discovery\DiscoveryQuery;
use App\Dto\Discovery\DiscoveryResult;
use App\Service\Discovery\Support\DiscoveryDocumentMatcher;
use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use App\ServiceInterface\Discovery\Document\DiscoveryDocumentProviderInterface;

final class MeiliDiscoveryAdapter implements DiscoveryAdapterInterface
{
    public function __construct(
        private readonly DiscoveryDocumentProviderInterface $documentProvider,
        private readonly DiscoveryDocumentMatcher $documentMatcher,
    ) {
    }

    public function discover(DiscoveryQuery $query): DiscoveryResult
    {
        return $this->documentMatcher->match(
            $this->documentProvider->provide($query->resourceType),
            $query,
        );
    }

    public function rebuild(?string $resourceType = null): void
    {
        $this->documentProvider->provide($resourceType);
    }

    public function getBackendName(): string
    {
        return 'meilisearch-demo';
    }
}
