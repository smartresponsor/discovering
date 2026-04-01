<?php

declare(strict_types=1);

namespace App\Service\Discovery\Playbook;

use App\Dto\Discovery\PlaybookManagementEntry;
use App\Dto\Discovery\PlaybookManagementFileEntry;
use App\Dto\Discovery\PlaybookManagementSurface;
use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;

final class PlaybookManagementSurfaceBuilder
{
    public function __construct(
        private readonly PlaybookFileDiscoverySourceRecordRepository $repository,
    ) {
    }

    public function build(): PlaybookManagementSurface
    {
        $records = $this->repository->all();
        $entries = array_map(
            static fn ($record): PlaybookManagementEntry => new PlaybookManagementEntry(
                resourceId: $record->resourceId,
                resourceType: $record->resourceType,
                title: $record->title,
                status: is_string($record->filters['status'] ?? null) ? $record->filters['status'] : 'unknown',
                visibility: is_string($record->filters['visibility'] ?? null) ? $record->filters['visibility'] : 'unknown',
                tags: is_array($record->metadata['tags'] ?? null) ? array_values(array_filter($record->metadata['tags'], 'is_string')) : [],
            ),
            $records,
        );

        usort($entries, static fn (PlaybookManagementEntry $left, PlaybookManagementEntry $right): int => $left->resourceId <=> $right->resourceId);

        $fileEntries = array_map(
            fn (string $path): PlaybookManagementFileEntry => new PlaybookManagementFileEntry(
                fileName: basename($path),
                path: $path,
                recordCount: count($this->repository->allFromStorageFile($path)),
            ),
            $this->repository->listStorageFiles(),
        );

        usort($fileEntries, static fn (PlaybookManagementFileEntry $left, PlaybookManagementFileEntry $right): int => $left->fileName <=> $right->fileName);

        return new PlaybookManagementSurface(
            sourceName: $this->repository->getSourceName(),
            storageDirectoryPath: $this->repository->getStorageDirectoryPath(),
            totalRecords: count($entries),
            totalFiles: count($fileEntries),
            entries: $entries,
            fileEntries: $fileEntries,
        );
    }
}
