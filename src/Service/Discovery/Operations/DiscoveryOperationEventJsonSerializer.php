<?php

declare(strict_types=1);

namespace App\Service\Discovery\Operations;

use App\Dto\Discovery\DiscoveryOperationEvent;

final class DiscoveryOperationEventJsonSerializer
{
    /**
     * @param list<DiscoveryOperationEvent> $events
     */
    public function encodeMany(array $events): string
    {
        $payload = array_map(
            static fn (DiscoveryOperationEvent $event): array => [
                'requestId' => $event->requestId,
                'channel' => $event->channel,
                'operation' => $event->operation,
                'status' => $event->status,
                'occurredAt' => $event->occurredAt,
                'context' => $event->context,
            ],
            $events,
        );

        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '[]';
    }

    /**
     * @return list<DiscoveryOperationEvent>
     */
    public function decodeMany(string $json): array
    {
        $payload = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        return array_map(
            static fn (array $item): DiscoveryOperationEvent => new DiscoveryOperationEvent(
                requestId: (string) ($item['requestId'] ?? ''),
                channel: (string) ($item['channel'] ?? 'http'),
                operation: (string) ($item['operation'] ?? ''),
                status: (string) ($item['status'] ?? 'ok'),
                occurredAt: (string) ($item['occurredAt'] ?? gmdate(DATE_ATOM)),
                context: is_array($item['context'] ?? null) ? $item['context'] : [],
            ),
            is_array($payload) ? $payload : [],
        );
    }
}
