<?php

declare(strict_types=1);

namespace App\Discovering\DTO;

/**
 * Represents the discovery state store descriptor contract used by discovery application, management, or state coordination flows.
 */
final readonly class DiscoveryStateStoreDescriptorDTO
{
    /**
     * @param list<string> $concerns
     */
    public function __construct(
        public string $nameEntity,
        public string $backend,
        public string $path,
        public string $storageMode,
        public bool $sharedConfigured,
        public bool $multiReplicaWriteReady,
        public array $concerns = [],
    ) {
    }

    /**
     * Serializes this discovery value into its canonical transport array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'nameEntity' => $this->nameEntity,
            'backend' => $this->backend,
            'path' => $this->path,
            'storageMode' => $this->storageMode,
            'sharedConfigured' => $this->sharedConfigured,
            'multiReplicaWriteReady' => $this->multiReplicaWriteReady,
            'concerns' => $this->concerns,
        ];
    }
}
