<?php

declare(strict_types=1);

namespace App\Dto\Discovery;


/**
 * Represents the briefing operator event contract used by discovery application, management, or state coordination flows.
 */
final class BriefingOperatorEvent extends DirectoryBackedFamilyOperatorEvent
{
    public static function fromGeneric(DirectoryBackedFamilyOperatorEvent $event): self
    {
        return new self(
            eventName: $event->eventName,
            level: $event->level,
            summary: $event->summary,
            context: $event->context,
        );
    }
}
