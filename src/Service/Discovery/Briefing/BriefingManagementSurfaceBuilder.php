<?php

declare(strict_types=1);

namespace App\Service\Discovery\Briefing;

use App\Dto\Discovery\BriefingManagementEntry;
use App\Dto\Discovery\BriefingManagementFileEntry;
use App\Dto\Discovery\BriefingManagementSurface;
use App\Service\Discovery\Source\Repository\BriefingFileDiscoverySourceRecordRepository;

final class BriefingManagementSurfaceBuilder
{
    public function __construct(
        private readonly BriefingFileDiscoverySourceRecordRepository $repository,
    ) {
    }

    public function build(): BriefingManagementSurface
    {
        $records = $this->repository->all();
        $entries = array_map(
            static fn ($record): BriefingManagementEntry => new BriefingManagementEntry(
                resourceId: $record->resourceId,
                resourceType: $record->resourceType,
                title: $record->title,
                status: is_string($record->filters['status'] ?? null) ? $record->filters['status'] : 'unknown',
                visibility: is_string($record->filters['visibility'] ?? null) ? $record->filters['visibility'] : 'unknown',
                tags: is_array($record->metadata['tags'] ?? null) ? array_values(array_filter($record->metadata['tags'], 'is_string')) : [],
            ),
            $records,
        );

        usort($entries, static fn (BriefingManagementEntry $left, BriefingManagementEntry $right): int => $left->resourceId <=> $right->resourceId);

        $fileEntries = array_map(
            fn (string $path): BriefingManagementFileEntry => new BriefingManagementFileEntry(
                fileName: basename($path),
                path: $path,
                recordCount: count($this->repository->allFromStorageFile($path)),
            ),
            $this->repository->listStorageFiles(),
        );

        usort($fileEntries, static fn (BriefingManagementFileEntry $left, BriefingManagementFileEntry $right): int => $left->fileName <=> $right->fileName);

        return new BriefingManagementSurface(
            sourceName: $this->repository->getSourceName(),
            storageDirectoryPath: $this->repository->getStorageDirectoryPath(),
            totalRecords: count($entries),
            totalFiles: count($fileEntries),
            entries: $entries,
            fileEntries: $fileEntries,
        );
    }
}
