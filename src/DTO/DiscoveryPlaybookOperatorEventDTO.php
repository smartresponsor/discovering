<?php

declare(strict_types=1);

namespace App\Discovering\DTO;

/**
 * Represents the playbook operator event contract used by discovery application, management, or state coordination flows.
 */
final readonly class DiscoveryPlaybookOperatorEventDTO extends DiscoveryDirectoryBackedFamilyOperatorEventDTO
{
    /**
     * Converts the generic directory-backed family value into this discovery-specific contract.
     */
    public static function fromGeneric(DiscoveryDirectoryBackedFamilyOperatorEventDTO $event): self
    {
        return new self(
            eventName: $event->eventName,
            level: $event->level,
            summary: $event->summary,
            context: $event->context,
        );
    }
}
