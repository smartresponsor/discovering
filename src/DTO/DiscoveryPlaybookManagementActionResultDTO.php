<?php

declare(strict_types=1);

namespace App\Discovering\DTO;

/**
 * Represents the playbook management action result contract used by discovery application, management, or state coordination flows.
 */
final readonly class DiscoveryPlaybookManagementActionResultDTO extends DiscoveryDirectoryBackedFamilyManagementActionResultDTO
{
    /**
     * Converts the generic directory-backed family value into this discovery-specific contract.
     */
    public static function fromGeneric(DiscoveryDirectoryBackedFamilyManagementActionResultDTO $result): self
    {
        return new self(
            actionName: $result->actionName,
            summary: $result->summary,
            payload: $result->payload,
        );
    }
}
