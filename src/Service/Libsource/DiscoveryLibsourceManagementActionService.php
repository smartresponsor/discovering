<?php

declare(strict_types=1);

namespace App\Discovering\Service\Libsource;

use App\Discovering\Builder\Libsource\DiscoveryLibsourceDiagnosticSurfaceBuilder;
use App\Discovering\DTO\DiscoveryLibsourceDiagnosticEntryDTO;
use App\Discovering\DTO\DiscoveryLibsourceManagementActionResultDTO;
use App\Discovering\DTO\DiscoveryLibsourceOperatorEventDTO;
use App\Discovering\ServiceInterface\Libsource\Log\DiscoveryLibsourceOperatorEventLogStoreInterface;

/**
 * Provides the libsource management action capability within the discovery component.
 */
final class DiscoveryLibsourceManagementActionService
{
    public function __construct(
        private readonly DiscoveryLibsourceDiagnosticSurfaceBuilder $diagnosticSurfaceBuilder,
        private readonly DiscoveryLibsourceOperatorEventLogStoreInterface $eventLogStore,
    ) {
    }

    /**
     * Performs the audit alignment operation for this discovery service.
     */
    public function auditAlignment(): DiscoveryLibsourceManagementActionResultDTO
    {
        $surface = $this->diagnosticSurfaceBuilder->build();
        $sourceCount = count($surface->entries);
        $recordCount = array_sum(array_map(
            static fn (DiscoveryLibsourceDiagnosticEntryDTO $entry): int => $entry->recordCount,
            $surface->entries,
        ));

        $result = new DiscoveryLibsourceManagementActionResultDTO(
            actionName: 'audit-alignment',
            summary: sprintf('Audited %d libsource entries covering %d records.', $sourceCount, $recordCount),
            payload: [
                'sourceCount' => $sourceCount,
                'recordCount' => $recordCount,
            ],
        );

        $this->eventLogStore->append(new DiscoveryLibsourceOperatorEventDTO(
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
    public function rebuildCoverageSnapshot(): DiscoveryLibsourceManagementActionResultDTO
    {
        $surface = $this->diagnosticSurfaceBuilder->build();

        $result = new DiscoveryLibsourceManagementActionResultDTO(
            actionName: 'rebuild-coverage',
            summary: sprintf('Rebuilt coverage snapshot for %d libsource entries.', count($surface->entries)),
            payload: [
                'sources' => array_map(
                    static fn (DiscoveryLibsourceDiagnosticEntryDTO $entry): string => $entry->sourceName,
                    $surface->entries,
                ),
            ],
        );

        $this->eventLogStore->append(new DiscoveryLibsourceOperatorEventDTO(
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
    public function inspect(string $sourceName): DiscoveryLibsourceManagementActionResultDTO
    {
        foreach ($this->diagnosticSurfaceBuilder->build()->entries as $entry) {
            if ($entry->sourceName !== $sourceName) {
                continue;
            }

            $result = new DiscoveryLibsourceManagementActionResultDTO(
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

            $this->eventLogStore->append(new DiscoveryLibsourceOperatorEventDTO(
                eventName: 'action:inspect',
                level: 'info',
                summary: $result->summary,
                context: $result->payload,
            ));

            return $result;
        }

        $result = new DiscoveryLibsourceManagementActionResultDTO(
            actionName: 'inspect',
            summary: sprintf('Source %s was not found.', $sourceName),
            payload: ['sourceName' => $sourceName],
        );

        $this->eventLogStore->append(new DiscoveryLibsourceOperatorEventDTO(
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
    public function clearEventLog(): DiscoveryLibsourceManagementActionResultDTO
    {
        $clearedCount = count($this->eventLogStore->all());
        $this->eventLogStore->clear();

        return new DiscoveryLibsourceManagementActionResultDTO(
            actionName: 'clear-event-log',
            summary: sprintf('Cleared %d stored action events from the libsource event log.', $clearedCount),
            payload: ['clearedCount' => $clearedCount],
        );
    }
}
