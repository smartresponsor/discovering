<?php

declare(strict_types=1);

namespace App\Discovering\DTO;

/**
 * Represents the briefing management surface contract used by discovery application, management, or state coordination flows.
 */
final readonly class DiscoveryBriefingManagementSurfaceDTO extends DiscoveryDirectoryBackedFamilyManagementSurfaceDTO
{
    /**
     * Converts the generic directory-backed family value into this discovery-specific contract.
     */
    public static function fromGeneric(DiscoveryDirectoryBackedFamilyManagementSurfaceDTO $surface): self
    {
        return new self(
            sourceName: $surface->sourceName,
            storageDirectoryPath: $surface->storageDirectoryPath,
            totalRecords: $surface->totalRecords,
            totalFiles: $surface->totalFiles,
            entries: array_map(
                static fn (DiscoveryDirectoryBackedFamilyManagementEntryDTO $entry): DiscoveryBriefingManagementEntryDTO => new DiscoveryBriefingManagementEntryDTO(
                    resourceId: $entry->resourceId,
                    resourceType: $entry->resourceType,
                    title: $entry->title,
                    status: $entry->status,
                    visibility: $entry->visibility,
                    tags: $entry->tags,
                ),
                $surface->entries,
            ),
            fileEntries: array_map(
                static fn (DiscoveryDirectoryBackedFamilyManagementFileEntryDTO $entry): DiscoveryBriefingManagementFileEntryDTO => new DiscoveryBriefingManagementFileEntryDTO(
                    fileName: $entry->fileName,
                    path: $entry->path,
                    recordCount: $entry->recordCount,
                ),
                $surface->fileEntries,
            ),
        );
    }
}
