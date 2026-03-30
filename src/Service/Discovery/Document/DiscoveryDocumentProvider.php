<?php

declare(strict_types=1);

namespace App\Service\Discovery\Document;

use App\ServiceInterface\Discovery\Document\DiscoveryDocumentProviderInterface;
use App\ServiceInterface\Discovery\Source\DiscoverySourceProviderInterface;
use App\ValueObject\Discovery\DiscoveryDocument;

final class DiscoveryDocumentProvider implements DiscoveryDocumentProviderInterface
{
    /**
     * @param iterable<DiscoverySourceProviderInterface> $sourceProviders
     */
    public function __construct(
        private readonly iterable $sourceProviders,
        private readonly DiscoveryDocumentFactory $documentFactory,
    ) {
    }

    public function provide(?string $resourceType = null): array
    {
        $documents = [];

        foreach ($this->sourceProviders as $sourceProvider) {
            if ($resourceType !== null && $resourceType !== '' && $sourceProvider->getResourceType() !== $resourceType) {
                continue;
            }

            foreach ($sourceProvider->provide() as $sourceRecord) {
                $documents[] = $this->documentFactory->fromSourceRecord($sourceRecord);
            }
        }

        return $documents;
    }
}
