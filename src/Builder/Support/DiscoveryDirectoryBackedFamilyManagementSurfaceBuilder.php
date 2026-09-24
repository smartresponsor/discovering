<?php

declare(strict_types=1);

namespace App\Discovering\Builder\Support;

use App\Discovering\DTO\DiscoveryDirectoryBackedFamilyManagementEntryDTO;
use App\Discovering\DTO\DiscoveryDirectoryBackedFamilyManagementFileEntryDTO;
use App\Discovering\DTO\DiscoveryDirectoryBackedFamilyManagementSurfaceDTO;
use App\Discovering\Repository\Source\DiscoveryAbstractDirectoryBackedSourceRecordRepository;

/**
 * Builds the directory backed family management surface output used by discovery management or diagnostics flows.
 */
final class DiscoveryDirectoryBackedFamilyManagementSurfaceBuilder
{
    /**
     * Builds the build result for this discovery workflow.
     */
    public function build(DiscoveryAbstractDirectoryBackedSourceRecordRepository $repository): DiscoveryDirectoryBackedFamilyManagementSurfaceDTO
    {
        $records = $repository->all();
        $entries = array_map(
            static fn ($record): DiscoveryDirectoryBackedFamilyManagementEntryDTO => new DiscoveryDirectoryBackedFamilyManagementEntryDTO(
                resourceId: $record->resourceId,
                resourceType: $record->resourceType,
                title: $record->title,
                status: is_string($record->filters['status'] ?? null) ? $record->filters['status'] : 'unknown',
                visibility: is_string($record->filters['visibility'] ?? null) ? $record->filters['visibility'] : 'unknown',
                tags: is_array($record->metadata['tags'] ?? null) ? array_values(array_filter($record->metadata['tags'], 'is_string')) : [],
            ),
            $records,
        );

        usort($entries, static fn (DiscoveryDirectoryBackedFamilyManagementEntryDTO $left, DiscoveryDirectoryBackedFamilyManagementEntryDTO $right): int => $left->resourceId <=> $right->resourceId);

        $fileEntries = array_map(
            fn (string $path): DiscoveryDirectoryBackedFamilyManagementFileEntryDTO => new DiscoveryDirectoryBackedFamilyManagementFileEntryDTO(
                fileName: basename($path),
                path: $path,
                recordCount: count($repository->allFromStorageFile($path)),
            ),
            $repository->listStorageFiles(),
        );

        usort($fileEntries, static fn (DiscoveryDirectoryBackedFamilyManagementFileEntryDTO $left, DiscoveryDirectoryBackedFamilyManagementFileEntryDTO $right): int => $left->fileName <=> $right->fileName);

        return new DiscoveryDirectoryBackedFamilyManagementSurfaceDTO(
            sourceName: $repository->getSourceName(),
            storageDirectoryPath: $repository->getStorageDirectoryPath(),
            totalRecords: count($entries),
            totalFiles: count($fileEntries),
            entries: $entries,
            fileEntries: $fileEntries,
        );
    }
}
