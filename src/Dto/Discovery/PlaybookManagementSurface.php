<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

/**
 * Represents the playbook management surface contract used by discovery application, management, or state coordination flows.
 */
final readonly class PlaybookManagementSurface extends DirectoryBackedFamilyManagementSurface
{
    public static function fromGeneric(DirectoryBackedFamilyManagementSurface $surface): self
    {
        return new self(
            sourceName: $surface->sourceName,
            storageDirectoryPath: $surface->storageDirectoryPath,
            totalRecords: $surface->totalRecords,
            totalFiles: $surface->totalFiles,
            entries: array_map(
                static fn (DirectoryBackedFamilyManagementEntry $entry): PlaybookManagementEntry => new PlaybookManagementEntry(
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
                static fn (DirectoryBackedFamilyManagementFileEntry $entry): PlaybookManagementFileEntry => new PlaybookManagementFileEntry(
                    fileName: $entry->fileName,
                    path: $entry->path,
                    recordCount: $entry->recordCount,
                ),
                $surface->fileEntries,
            ),
        );
    }
}
