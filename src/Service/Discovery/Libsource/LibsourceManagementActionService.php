<?php

declare(strict_types=1);

namespace App\Service\Discovery\Libsource;

use App\Dto\Discovery\LibsourceDiagnosticEntry;
use App\Dto\Discovery\LibsourceManagementActionResult;
use App\Dto\Discovery\LibsourceOperatorEvent;
use App\ServiceInterface\Discovery\Libsource\Log\LibsourceOperatorEventLogStoreInterface;

/**
 * Provides the libsource management action capability within the discovery component.
 */
final class LibsourceManagementActionService
{
    public function __construct(
        private readonly LibsourceDiagnosticSurfaceBuilder $diagnosticSurfaceBuilder,
        private readonly LibsourceOperatorEventLogStoreInterface $eventLogStore,
    ) {
    }

    /**
     * Performs the audit alignment operation for this discovery service.
     */
    public function auditAlignment(): LibsourceManagementActionResult
    {
        $surface = $this->diagnosticSurfaceBuilder->build();
        $sourceCount = count($surface->entries);
        $recordCount = array_sum(array_map(
            static fn (LibsourceDiagnosticEntry $entry): int => $entry->recordCount,
            $surface->entries,
        ));

        $result = new LibsourceManagementActionResult(
            actionName: 'audit-alignment',
            summary: sprintf('Audited %d libsource entries covering %d records.', $sourceCount, $recordCount),
            payload: [
                'sourceCount' => $sourceCount,
                'recordCount' => $recordCount,
            ],
        );

        $this->eventLogStore->append(new LibsourceOperatorEvent(
            eventName: 'action:audit-alignment',
            level: 'info',
            summary: $result->summary,
            context: $result->payload,
        ));

        return $result;
    }

    /**
     * Performs the rebuild coverage snapshot operation for this discovery service.
     */
    public function rebuildCoverageSnapshot(): LibsourceManagementActionResult
    {
        $surface = $this->diagnosticSurfaceBuilder->build();

        $result = new LibsourceManagementActionResult(
            actionName: 'rebuild-coverage',
            summary: sprintf('Rebuilt coverage snapshot for %d libsource entries.', count($surface->entries)),
            payload: [
                'sources' => array_map(
                    static fn (LibsourceDiagnosticEntry $entry): string => $entry->sourceName,
                    $surface->entries,
                ),
            ],
        );

        $this->eventLogStore->append(new LibsourceOperatorEvent(
            eventName: 'action:rebuild-coverage',
            level: 'info',
            summary: $result->summary,
            context: $result->payload,
        ));

        return $result;
    }

    /**
     * Performs the inspect operation for this discovery service.
     */
    public function inspect(string $sourceName): LibsourceManagementActionResult
    {
        foreach ($this->diagnosticSurfaceBuilder->build()->entries as $entry) {
            if ($entry->sourceName !== $sourceName) {
                continue;
            }

            $result = new LibsourceManagementActionResult(
                actionName: 'inspect',
                summary: sprintf('Inspected %s.', $sourceName),
                payload: [
                    'sourceName' => $entry->sourceName,
                    'resourceType' => $entry->resourceType,
                    'providerClass' => $entry->providerClass,
                    'repositoryClass' => $entry->repositoryClass,
                    'recordCount' => $entry->recordCount,
                    'sampleResourceIds' => $entry->sampleResourceIds,
                ],
            );

            $this->eventLogStore->append(new LibsourceOperatorEvent(
                eventName: 'action:inspect',
                level: 'info',
                summary: $result->summary,
                context: $result->payload,
            ));

            return $result;
        }

        $result = new LibsourceManagementActionResult(
            actionName: 'inspect',
            summary: sprintf('Source %s was not found.', $sourceName),
            payload: ['sourceName' => $sourceName],
        );

        $this->eventLogStore->append(new LibsourceOperatorEvent(
            eventName: 'action:inspect',
            level: 'warning',
            summary: $result->summary,
            context: $result->payload,
        ));

        return $result;
    }

    /**
     * Performs the clear event log operation for this discovery service.
     */
    public function clearEventLog(): LibsourceManagementActionResult
    {
        $clearedCount = count($this->eventLogStore->all());
        $this->eventLogStore->clear();

        return new LibsourceManagementActionResult(
            actionName: 'clear-event-log',
            summary: sprintf('Cleared %d stored action events from the libsource event log.', $clearedCount),
            payload: ['clearedCount' => $clearedCount],
        );
    }
}
