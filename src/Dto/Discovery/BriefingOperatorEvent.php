<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

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
