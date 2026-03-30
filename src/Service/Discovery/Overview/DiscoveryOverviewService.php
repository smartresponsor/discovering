<?php

declare(strict_types=1);

namespace App\Service\Discovery\Overview;

use App\Dto\Discovery\DiscoveryOverview;
use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use App\ServiceInterface\Discovery\Document\DiscoveryDocumentProviderInterface;
use App\ServiceInterface\Discovery\Overview\DiscoveryOverviewServiceInterface;

final class DiscoveryOverviewService implements DiscoveryOverviewServiceInterface
{
    public function __construct(
        private readonly DiscoveryDocumentProviderInterface $documentProvider,
        private readonly DiscoveryAdapterInterface $adapter,
    ) {
    }

    public function buildOverview(): DiscoveryOverview
    {
        $documents = $this->documentProvider->provide();
        $countsByResourceType = [];

        foreach ($documents as $document) {
            $countsByResourceType[$document->resourceType] ??= 0;
            ++$countsByResourceType[$document->resourceType];
        }

        ksort($countsByResourceType);

        return new DiscoveryOverview(
            backendName: $this->adapter->getBackendName(),
            totalDocuments: count($documents),
            countsByResourceType: $countsByResourceType,
            sampleDocuments: array_slice($documents, 0, 5),
        );
    }
}
