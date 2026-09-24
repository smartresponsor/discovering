<?php

declare(strict_types=1);

namespace App\Discovering\Service\Overview;

use App\Discovering\DTO\DiscoveryOverviewDTO;
use App\Discovering\ServiceInterface\Backend\DiscoveryBackendInterface;
use App\Discovering\ServiceInterface\Document\DiscoveryDocumentProviderInterface;
use App\Discovering\ServiceInterface\Overview\DiscoveryOverviewServiceInterface;
use App\Discovering\ServiceInterface\Source\DiscoverySourceProviderInterface;

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
        private readonly DiscoveryBackendInterface $adapter,
        private readonly iterable $sourceProviders,
    ) {
    }

    /**
     * Builds the overview result for this discovery workflow.
     */
    public function buildOverview(): DiscoveryOverviewDTO
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

        return new DiscoveryOverviewDTO(
            backendName: $this->adapter->getBackendName(),
            totalDocuments: count($documents),
            countsByResourceType: $countsByResourceType,
            countsBySourceName: $countsBySourceName,
            sampleDocuments: array_slice($documents, 0, 5),
        );
    }
}
