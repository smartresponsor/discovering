<?php

declare(strict_types=1);

namespace App\Service\Discovery\Operations;

use App\Dto\Discovery\DiscoveryOperationEvent;

final class FileDiscoveryOperationEventLogStore implements DiscoveryOperationEventLogStoreInterface
{
    public function __construct(
        private readonly string $path,
        private readonly DiscoveryOperationEventJsonSerializer $serializer,
    ) {
    }

    public function append(DiscoveryOperationEvent $event): void
    {
        $events = $this->all();
        $events[] = $event;
        $this->persist($events);
    }

    public function all(): array
    {
        if (!is_file($this->path)) {
            return [];
        }

        $contents = file_get_contents($this->path);
        if ($contents === false || trim($contents) === '') {
            return [];
        }

        return $this->serializer->decodeMany($contents);
    }

    public function latest(int $limit = 25): array
    {
        if ($limit <= 0) {
            return [];
        }

        $events = $this->all();
        if ($events === []) {
            return [];
        }

        return array_values(array_slice($events, -$limit));
    }

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
