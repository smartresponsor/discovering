<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

final class BriefingManagementActionResult extends DirectoryBackedFamilyManagementActionResult
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
