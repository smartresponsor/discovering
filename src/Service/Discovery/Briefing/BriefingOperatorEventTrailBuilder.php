<?php

declare(strict_types=1);

namespace App\Service\Discovery\Briefing;

use App\Dto\Discovery\BriefingManagementActionResult;
use App\Dto\Discovery\BriefingOperatorEvent;
use App\Service\Discovery\Source\Repository\BriefingFileDiscoverySourceRecordRepository;

final class BriefingOperatorEventTrailBuilder
{
    public function __construct(
        private readonly BriefingFileDiscoverySourceRecordRepository $repository,
    ) {
    }

    /**
     * @return list<BriefingOperatorEvent>
     */
    public function build(?BriefingManagementActionResult $lastActionResult = null): array
    {
        $files = $this->repository->listStorageFiles();
        $records = $this->repository->all();
        $legacyPath = $this->repository->getLegacyStoragePath();
        $legacyExists = is_file($legacyPath);

        $events = [
            new BriefingOperatorEvent(
                eventName: 'surface:load',
                level: $files === [] ? 'warning' : 'info',
                summary: sprintf('Briefing surface loaded with %d files and %d records.', count($files), count($records)),
                context: [
                    'storageDirectoryPath' => $this->repository->getStorageDirectoryPath(),
                    'legacyStoragePath' => $legacyPath,
                    'legacyExists' => $legacyExists,
                ],
            ),
        ];

        if ($lastActionResult !== null) {
            $events[] = new BriefingOperatorEvent(
                eventName: sprintf('action:%s', $lastActionResult->actionName),
                level: $this->inferActionLevel($lastActionResult),
                summary: $lastActionResult->summary,
                context: $lastActionResult->payload,
            );
        }

        if ($files === []) {
            $events[] = new BriefingOperatorEvent(
                eventName: 'registry:empty',
                level: 'warning',
                summary: 'Briefing registry currently has no source files.',
                context: [
                    'storageDirectoryPath' => $this->repository->getStorageDirectoryPath(),
                    'legacyStoragePath' => $legacyPath,
                    'legacyExists' => $legacyExists,
                ],
            );

            return $events;
        }

        foreach ($files as $path) {
            $events[] = new BriefingOperatorEvent(
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

    private function inferActionLevel(BriefingManagementActionResult $result): string
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
