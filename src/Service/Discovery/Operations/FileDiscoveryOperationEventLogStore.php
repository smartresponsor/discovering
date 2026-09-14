<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Operations;

use App\Discovering\Dto\Discovery\DiscoveryOperationEvent;
use App\Discovering\ServiceInterface\Discovery\Operations\DiscoveryOperationEventLogStoreInterface;

/**
 * Provides the file discovery operation event log store capability within the discovery component.
 */
final class FileDiscoveryOperationEventLogStore implements DiscoveryOperationEventLogStoreInterface
{
    public function __construct(
        private readonly string $path,
        private readonly DiscoveryOperationEventJsonSerializer $serializer,
    ) {
    }

    /**
     * Performs the append operation for this discovery service.
     */
    public function append(DiscoveryOperationEvent $event): void
    {
        $events = $this->all();
        $events[] = $event;
        $this->persist($events);
    }

    /**
     * Performs the all operation for this discovery service.
     */
    public function all(): array
    {
        if (!is_file($this->path)) {
            return [];
        }

        $contents = file_get_contents($this->path);
        if (false === $contents || '' === trim($contents)) {
            return [];
        }

        return $this->serializer->decodeMany($contents);
    }

    /**
     * Performs the latest operation for this discovery service.
     */
    public function latest(int $limit = 25): array
    {
        if ($limit <= 0) {
            return [];
        }

        $events = $this->all();
        if ([] === $events) {
            return [];
        }

        return array_values(array_slice($events, -$limit));
    }

    /**
     * Performs the clear operation for this discovery service.
     */
    public function clear(): void
    {
        $this->persist([]);
    }

    /**
     * @param list<DiscoveryOperationEvent> $events
     */
    private function persist(array $events): void
    {
        $directory = dirname($this->path);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($this->path, $this->serializer->encodeMany($events), LOCK_EX);
    }
}
