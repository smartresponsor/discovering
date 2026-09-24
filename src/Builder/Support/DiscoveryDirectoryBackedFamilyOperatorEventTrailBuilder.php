<?php

declare(strict_types=1);

namespace App\Discovering\Builder\Support;

use App\Discovering\DTO\DiscoveryDirectoryBackedFamilyManagementActionResultDTO;
use App\Discovering\DTO\DiscoveryDirectoryBackedFamilyOperatorEventDTO;
use App\Discovering\Repository\Source\DiscoveryAbstractDirectoryBackedSourceRecordRepository;

/**
 * Builds the directory backed family operator event trail output used by discovery management or diagnostics flows.
 */
final class DiscoveryDirectoryBackedFamilyOperatorEventTrailBuilder
{
    public function __construct(
        private readonly DiscoveryAbstractDirectoryBackedSourceRecordRepository $repository,
        private readonly string $familyLabel,
    ) {
    }

    /**
     * @return list<DiscoveryDirectoryBackedFamilyOperatorEventDTO>
     */
    public function build(?DiscoveryDirectoryBackedFamilyManagementActionResultDTO $lastActionResult = null): array
    {
        $files = $this->repository->listStorageFiles();
        $records = $this->repository->all();
        $legacyPath = $this->repository->getLegacyStoragePath();
        $legacyExists = is_file($legacyPath);

        $events = [
            new DiscoveryDirectoryBackedFamilyOperatorEventDTO(
                eventName: 'surface:load',
                level: [] === $files ? 'warning' : 'info',
                summary: sprintf('%s surface loaded with %d files and %d records.', ucfirst($this->familyLabel), count($files), count($records)),
                context: [
                    'storageDirectoryPath' => $this->repository->getStorageDirectoryPath(),
                    'legacyStoragePath' => $legacyPath,
                    'legacyExists' => $legacyExists,
                ],
            ),
        ];

        if (null !== $lastActionResult) {
            $events[] = new DiscoveryDirectoryBackedFamilyOperatorEventDTO(
                eventName: sprintf('action:%s', $lastActionResult->actionName),
                level: $this->inferActionLevel($lastActionResult),
                summary: $lastActionResult->summary,
                context: $lastActionResult->payload,
            );
        }

        if ([] === $files) {
            $events[] = new DiscoveryDirectoryBackedFamilyOperatorEventDTO(
                eventName: 'registry:empty',
                level: 'warning',
                summary: sprintf('%s registry currently has no source files.', ucfirst($this->familyLabel)),
                context: [
                    'storageDirectoryPath' => $this->repository->getStorageDirectoryPath(),
                    'legacyStoragePath' => $legacyPath,
                    'legacyExists' => $legacyExists,
                ],
            );

            return $events;
        }

        foreach ($files as $path) {
            $events[] = new DiscoveryDirectoryBackedFamilyOperatorEventDTO(
                eventName: 'registry:file',
                level: 'info',
                summary: sprintf('%s contributes %d records.', basename($path), count($this->repository->allFromStorageFile($path))),
                context: [
                    'path' => $path,
                ],
            );
        }

        return $events;
    }

    private function inferActionLevel(DiscoveryDirectoryBackedFamilyManagementActionResultDTO $result): string
    {
        if (($result->payload['migrated'] ?? null) === false) {
            return 'warning';
        }

        if (($result->payload['created'] ?? null) === false) {
            return 'warning';
        }

        return 'info';
    }
}
