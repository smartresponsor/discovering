<?php

declare(strict_types=1);

namespace App\Dto\Discovery;


/**
 * Represents the discovery state store descriptor contract used by discovery application, management, or state coordination flows.
 */
final class DiscoveryStateStoreDescriptor
{
    /**
     * @param list<string> $concerns
     */
    public function __construct(
        public string $name,
        public string $backend,
        public string $path,
        public string $storageMode,
        public bool $sharedConfigured,
        public bool $multiReplicaWriteReady,
        public array $concerns = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'backend' => $this->backend,
            'path' => $this->path,
            'storageMode' => $this->storageMode,
            'sharedConfigured' => $this->sharedConfigured,
            'multiReplicaWriteReady' => $this->multiReplicaWriteReady,
            'concerns' => $this->concerns,
        ];
    }
}
