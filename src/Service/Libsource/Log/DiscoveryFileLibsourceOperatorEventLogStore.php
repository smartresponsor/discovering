<?php

declare(strict_types=1);

namespace App\Discovering\Service\Libsource\Log;

use App\Discovering\DTO\DiscoveryLibsourceOperatorEventDTO;
use App\Discovering\ServiceInterface\Libsource\Log\DiscoveryLibsourceOperatorEventLogStoreInterface;

/**
 * Provides the file libsource operator event log store capability within the discovery component.
 */
final class DiscoveryFileLibsourceOperatorEventLogStore implements DiscoveryLibsourceOperatorEventLogStoreInterface
{
    public function __construct(
        private readonly string $path,
        private readonly DiscoveryLibsourceOperatorEventJsonSerializer $serializer,
    ) {
    }

    /**
     * Performs the append operation for this discovery service.
     */
    public function append(DiscoveryLibsourceOperatorEventDTO $event): void
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
     * Performs the clear operation for this discovery service.
     */
    public function clear(): void
    {
        $this->persist([]);
    }

    /**
     * @param list<DiscoveryLibsourceOperatorEventDTO> $events
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
