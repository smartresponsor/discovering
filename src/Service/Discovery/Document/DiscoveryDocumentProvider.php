<?php

declare(strict_types=1);

namespace App\Service\Discovery\Document;

use App\ServiceInterface\Discovery\Document\DiscoveryDocumentProviderInterface;
use App\ServiceInterface\Discovery\Source\DiscoverySourceProviderInterface;
use App\ValueObject\Discovery\DiscoveryDocument;


/**
 * Provides the discovery document provider capability within the discovery component.
 */
final class DiscoveryDocumentProvider implements DiscoveryDocumentProviderInterface
{
    /**
     * @param iterable<DiscoverySourceProviderInterface> $sourceProviders
     */
    public function __construct(
        private readonly iterable $sourceProviders,
        private readonly DiscoveryDocumentFactory $documentFactory = new DiscoveryDocumentFactory(),
    ) {
    }

    /**
     * @return list<DiscoveryDocument>
     */
    public function provide(): array
    {
        $documents = [];

        foreach ($this->sourceProviders as $sourceProvider) {
            foreach ($sourceProvider->provide() as $record) {
                $documents[] = $this->documentFactory->createFromSourceRecord($record);
            }
        }

        usort(
            $documents,
            static fn (DiscoveryDocument $left, DiscoveryDocument $right): int => [$left->resource, $left->id] <=> [$right->resource, $right->id],
        );

        return $documents;
    }
}
