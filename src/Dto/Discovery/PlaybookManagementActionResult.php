<?php

declare(strict_types=1);

namespace App\Dto\Discovery;


/**
 * Represents the playbook management action result contract used by discovery application, management, or state coordination flows.
 */
final class PlaybookManagementActionResult extends DirectoryBackedFamilyManagementActionResult
{
    public static function fromGeneric(DirectoryBackedFamilyManagementActionResult $result): self
    {
        return new self(
            actionName: $result->actionName,
            summary: $result->summary,
            payload: $result->payload,
        );
    }
}
