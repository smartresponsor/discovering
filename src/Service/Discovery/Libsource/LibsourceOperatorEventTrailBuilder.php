<?php

declare(strict_types=1);

namespace App\Service\Discovery\Libsource;

use App\Dto\Discovery\LibsourceManagementActionResult;
use App\Dto\Discovery\LibsourceOperatorEvent;
use App\Service\Discovery\Libsource\Log\LibsourceOperatorEventLogStoreInterface;

final class LibsourceOperatorEventTrailBuilder
{
    public function __construct(
        private readonly LibsourceDiagnosticSurfaceBuilder $diagnosticSurfaceBuilder,
        private readonly LibsourceOperatorEventLogStoreInterface $eventLogStore,
    ) {
    }

    /**
     * @return list<LibsourceOperatorEvent>
     */
    public function build(?LibsourceManagementActionResult $lastActionResult = null): array
    {
        $surface = $this->diagnosticSurfaceBuilder->build();
        $events = [
            new LibsourceOperatorEvent(
                eventName: 'surface:load',
                level: 'info',
                summary: sprintf('Libsource surface loaded with %d diagnostic entries and %d stored action events.', count($surface->entries), count($this->eventLogStore->all())),
            ),
        ];

        if ($lastActionResult !== null) {
            $events[] = new LibsourceOperatorEvent(
                eventName: sprintf('action:%s', $lastActionResult->actionName),
                level: 'info',
                summary: $lastActionResult->summary,
                context: $lastActionResult->payload,
            );
        }

        foreach ($this->eventLogStore->all() as $storedEvent) {
            if ($lastActionResult !== null
                && $storedEvent->eventName === sprintf('action:%s', $lastActionResult->actionName)
                && $storedEvent->summary === $lastActionResult->summary
            ) {
                continue;
            }

            $events[] = $storedEvent;
        }

        foreach ($surface->entries as $entry) {
            $events[] = new LibsourceOperatorEvent(
                eventName: 'coverage:source',
                level: 'info',
                summary: sprintf('%s exposes %d records.', $entry->sourceName, $entry->recordCount),
                context: [
                    'resourceType' => $entry->resourceType,
                    'providerClass' => $entry->providerClass,
                    'repositoryClass' => $entry->repositoryClass,
                    'sampleResourceIds' => $entry->sampleResourceIds,
                ],
            );
        }

        return $events;
    }
}
