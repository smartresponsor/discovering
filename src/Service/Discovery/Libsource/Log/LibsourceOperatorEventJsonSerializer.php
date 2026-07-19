<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Libsource\Log;

use App\Discovering\Dto\Discovery\LibsourceOperatorEvent;

/**
 * Handles libsource operator event json serializer concerns for discovery state, source, or API payloads.
 */
final class LibsourceOperatorEventJsonSerializer
{
    /**
     * @param list<LibsourceOperatorEvent> $events
     */
    public function encodeMany(array $events): string
    {
        $payload = array_map(
            static fn (LibsourceOperatorEvent $event): array => [
                'eventName' => $event->eventName,
                'level' => $event->level,
                'summary' => $event->summary,
                'context' => $event->context,
            ],
            $events,
        );

        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '[]';
    }

    /**
     * @return list<LibsourceOperatorEvent>
     */
    public function decodeMany(string $json): array
    {
        $payload = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        return array_map(
            static fn (array $item): LibsourceOperatorEvent => new LibsourceOperatorEvent(
                eventName: (string) ($item['eventName'] ?? ''),
                level: (string) ($item['level'] ?? 'info'),
                summary: (string) ($item['summary'] ?? ''),
                context: is_array($item['context'] ?? null) ? $item['context'] : [],
            ),
            is_array($payload) ? $payload : [],
        );
    }
}
