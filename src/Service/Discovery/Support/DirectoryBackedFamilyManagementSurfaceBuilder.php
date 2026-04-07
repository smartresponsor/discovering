<?php

declare(strict_types=1);

namespace App\Service\Discovery\Support;

use App\Dto\Discovery\DirectoryBackedFamilyManagementEntry;
use App\Dto\Discovery\DirectoryBackedFamilyManagementFileEntry;
use App\Dto\Discovery\DirectoryBackedFamilyManagementSurface;
use App\Service\Discovery\Source\Repository\AbstractDirectoryBackedDiscoverySourceRecordRepository;


/**
 * Builds the directory backed family management surface output used by discovery management or diagnostics flows.
 */
final class DirectoryBackedFamilyManagementSurfaceBuilder
{
    /**
     * Builds the build result for this discovery workflow.
     */
    public function build(AbstractDirectoryBackedDiscoverySourceRecordRepository $repository): DirectoryBackedFamilyManagementSurface
    {
        $records = $repository->all();
        $entries = array_map(
            static fn ($record): DirectoryBackedFamilyManagementEntry => new DirectoryBackedFamilyManagementEntry(
                resourceId: $record->resourceId,
                resourceType: $record->resourceType,
                title: $record->title,
                status: is_string($record->filters['status'] ?? null) ? $record->filters['status'] : 'unknown',
                visibility: is_string($record->filters['visibility'] ?? null) ? $record->filters['visibility'] : 'unknown',
                tags: is_array($record->metadata['tags'] ?? null) ? array_values(array_filter($record->metadata['tags'], 'is_string')) : [],
            ),
            $records,
        );

        usort($entries, static fn (DirectoryBackedFamilyManagementEntry $left, DirectoryBackedFamilyManagementEntry $right): int => $left->resourceId <=> $right->resourceId);

        $fileEntries = array_map(
            fn (string $path): DirectoryBackedFamilyManagementFileEntry => new DirectoryBackedFamilyManagementFileEntry(
                fileName: basename($path),
                path: $path,
                recordCount: count($repository->allFromStorageFile($path)),
            ),
            $repository->listStorageFiles(),
        );

        usort($fileEntries, static fn (DirectoryBackedFamilyManagementFileEntry $left, DirectoryBackedFamilyManagementFileEntry $right): int => $left->fileName <=> $right->fileName);

        return new DirectoryBackedFamilyManagementSurface(
            sourceName: $repository->getSourceName(),
            storageDirectoryPath: $repository->getStorageDirectoryPath(),
            totalRecords: count($entries),
            totalFiles: count($fileEntries),
            entries: $entries,
            fileEntries: $fileEntries,
        );
    }
}
