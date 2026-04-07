<?php

declare(strict_types=1);

namespace App\Service\Discovery\Libsource;

use App\Dto\Discovery\LibsourceDiagnosticEntry;
use App\Dto\Discovery\LibsourceDiagnosticSurface;
use App\Service\Discovery\Source\Repository\DiscoverySourceRepositoryRegistry;
use App\ServiceInterface\Discovery\Source\DiscoverySourceProviderInterface;


/**
 * Builds the libsource diagnostic surface output used by discovery management or diagnostics flows.
 */
final class LibsourceDiagnosticSurfaceBuilder
{
    /**
     * @param iterable<DiscoverySourceProviderInterface> $sourceProviders
     */
    public function __construct(
        private readonly iterable $sourceProviders,
        private readonly DiscoverySourceRepositoryRegistry $repositoryRegistry,
    ) {
    }

    /**
     * Builds the build result for this discovery workflow.
     */
    public function build(): LibsourceDiagnosticSurface
    {
        $entries = [];

        foreach ($this->sourceProviders as $sourceProvider) {
            $sourceName = $sourceProvider->getSourceName();
            $repository = $this->repositoryRegistry->getBySourceName($sourceName);
            $records = $repository->all();

            $entries[] = new LibsourceDiagnosticEntry(
                sourceName: $sourceName,
                resourceType: $sourceProvider->getResourceType(),
                providerClass: $sourceProvider::class,
                repositoryClass: $repository::class,
                recordCount: count($records),
                sampleResourceIds: array_slice(array_map(
                    static fn ($record): string => $record->resourceId,
                    $records,
                ), 0, 3),
            );
        }

        usort($entries, static fn (LibsourceDiagnosticEntry $left, LibsourceDiagnosticEntry $right): int => $left->sourceName <=> $right->sourceName);

        return new LibsourceDiagnosticSurface($entries);
    }
}
