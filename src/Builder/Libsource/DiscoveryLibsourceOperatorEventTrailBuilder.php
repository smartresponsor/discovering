<?php

declare(strict_types=1);

namespace App\Discovering\Builder\Libsource;

use App\Discovering\DTO\DiscoveryLibsourceManagementActionResultDTO;
use App\Discovering\DTO\DiscoveryLibsourceOperatorEventDTO;
use App\Discovering\ServiceInterface\Libsource\Log\DiscoveryLibsourceOperatorEventLogStoreInterface;

/**
 * Builds the libsource operator event trail output used by discovery management or diagnostics flows.
 */
final class DiscoveryLibsourceOperatorEventTrailBuilder
{
    public function __construct(
        private readonly DiscoveryLibsourceDiagnosticSurfaceBuilder $diagnosticSurfaceBuilder,
        private readonly DiscoveryLibsourceOperatorEventLogStoreInterface $eventLogStore,
    ) {
    }

    /**
     * @return list<DiscoveryLibsourceOperatorEventDTO>
     */
    public function build(?DiscoveryLibsourceManagementActionResultDTO $lastActionResult = null): array
    {
        $surface = $this->diagnosticSurfaceBuilder->build();
        $events = [
            new DiscoveryLibsourceOperatorEventDTO(
                eventName: 'surface:load',
                level: 'info',
                summary: sprintf('Libsource surface loaded with %d diagnostic entries and %d stored action events.', count($surface->entries), count($this->eventLogStore->all())),
            ),
        ];

        if (null !== $lastActionResult) {
            $events[] = new DiscoveryLibsourceOperatorEventDTO(
                eventName: sprintf('action:%s', $lastActionResult->actionName),
                level: 'info',
                summary: $lastActionResult->summary,
                context: $lastActionResult->payload,
            );
        }

        foreach ($this->eventLogStore->all() as $storedEvent) {
            if (null !== $lastActionResult
                && $storedEvent->eventName === sprintf('action:%s', $lastActionResult->actionName)
                && $storedEvent->summary === $lastActionResult->summary
            ) {
                continue;
            }

            $events[] = $storedEvent;
        }

        foreach ($surface->entries as $entry) {
            $events[] = new DiscoveryLibsourceOperatorEventDTO(
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
