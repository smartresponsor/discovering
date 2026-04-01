<?php

declare(strict_types=1);

namespace App\Service\Discovery\Playbook;

use App\Dto\Discovery\PlaybookManagementActionResult;
use App\Dto\Discovery\PlaybookOperatorEvent;
use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;

final class PlaybookOperatorEventTrailBuilder
{
    public function __construct(
        private readonly PlaybookFileDiscoverySourceRecordRepository $repository,
    ) {
    }

    /**
     * @return list<PlaybookOperatorEvent>
     */
    public function build(?PlaybookManagementActionResult $lastActionResult = null): array
    {
        $files = $this->repository->listStorageFiles();
        $records = $this->repository->all();
        $legacyPath = $this->repository->getLegacyStoragePath();
        $legacyExists = is_file($legacyPath);

        $events = [
            new PlaybookOperatorEvent(
                eventName: 'surface:load',
                level: $files === [] ? 'warning' : 'info',
                summary: sprintf('Playbook surface loaded with %d files and %d records.', count($files), count($records)),
                context: [
                    'storageDirectoryPath' => $this->repository->getStorageDirectoryPath(),
                    'legacyStoragePath' => $legacyPath,
                    'legacyExists' => $legacyExists,
                ],
            ),
        ];

        if ($lastActionResult !== null) {
            $events[] = new PlaybookOperatorEvent(
                eventName: sprintf('action:%s', $lastActionResult->actionName),
                level: $this->inferActionLevel($lastActionResult),
                summary: $lastActionResult->summary,
                context: $lastActionResult->payload,
            );
        }

        if ($files === []) {
            $events[] = new PlaybookOperatorEvent(
                eventName: 'registry:empty',
                level: 'warning',
                summary: 'Playbook registry currently has no source files.',
                context: [
                    'storageDirectoryPath' => $this->repository->getStorageDirectoryPath(),
                    'legacyStoragePath' => $legacyPath,
                    'legacyExists' => $legacyExists,
                ],
            );

            return $events;
        }

        foreach ($files as $path) {
            $events[] = new PlaybookOperatorEvent(
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

    private function inferActionLevel(PlaybookManagementActionResult $result): string
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
