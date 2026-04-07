<?php

declare(strict_types=1);

namespace App\Service\Discovery\Overview;

use App\Dto\Discovery\DiscoveryOverview;
use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use App\ServiceInterface\Discovery\Document\DiscoveryDocumentProviderInterface;
use App\ServiceInterface\Discovery\Overview\DiscoveryOverviewServiceInterface;
use App\ServiceInterface\Discovery\Source\DiscoverySourceProviderInterface;


/**
 * Provides the discovery overview capability within the discovery component.
 */
final class DiscoveryOverviewService implements DiscoveryOverviewServiceInterface
{
    /**
     * @param iterable<DiscoverySourceProviderInterface> $sourceProviders
     */
    public function __construct(
        private readonly DiscoveryDocumentProviderInterface $documentProvider,
        private readonly DiscoveryAdapterInterface $adapter,
        private readonly iterable $sourceProviders,
    ) {
    }

    /**
     * Builds the overview result for this discovery workflow.
     */
    public function buildOverview(): DiscoveryOverview
    {
        $documents = $this->documentProvider->provide();
        $countsByResourceType = [];
        $countsBySourceName = [];

        foreach ($documents as $document) {
            $countsByResourceType[$document->resource] ??= 0;
            ++$countsByResourceType[$document->resource];
        }

        foreach ($this->sourceProviders as $sourceProvider) {
            $countsBySourceName[$sourceProvider->getSourceName()] = count($sourceProvider->provide());
        }

        ksort($countsByResourceType);
        ksort($countsBySourceName);

        return new DiscoveryOverview(
            backendName: $this->adapter->getBackendName(),
            totalDocuments: count($documents),
            countsByResourceType: $countsByResourceType,
            countsBySourceName: $countsBySourceName,
            sampleDocuments: array_slice($documents, 0, 5),
        );
    }
}
