<?php

declare(strict_types=1);

namespace App\Discovering\Builder\Libsource;

use App\Discovering\DTO\DiscoveryLibsourceDiagnosticEntryDTO;
use App\Discovering\DTO\DiscoveryLibsourceDiagnosticSurfaceDTO;
use App\Discovering\Service\Source\Repository\DiscoverySourceRepositoryRegistry;
use App\Discovering\ServiceInterface\Source\DiscoverySourceProviderInterface;

/**
 * Builds the libsource diagnostic surface output used by discovery management or diagnostics flows.
 */
final class DiscoveryLibsourceDiagnosticSurfaceBuilder
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
    public function build(): DiscoveryLibsourceDiagnosticSurfaceDTO
    {
        $entries = [];

        foreach ($this->sourceProviders as $sourceProvider) {
            $sourceName = $sourceProvider->getSourceName();
            $repository = $this->repositoryRegistry->getBySourceName($sourceName);
            $records = $repository->all();

            $entries[] = new DiscoveryLibsourceDiagnosticEntryDTO(
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

        usort($entries, static fn (DiscoveryLibsourceDiagnosticEntryDTO $left, DiscoveryLibsourceDiagnosticEntryDTO $right): int => $left->sourceName <=> $right->sourceName);

        return new DiscoveryLibsourceDiagnosticSurfaceDTO($entries);
    }
}
