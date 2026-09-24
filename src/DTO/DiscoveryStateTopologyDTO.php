<?php

declare(strict_types=1);

namespace App\Discovering\DTO;

/**
 * Represents the discovery state topology contract used by discovery application, management, or state coordination flows.
 */
final readonly class DiscoveryStateTopologyDTO
{
    /**
     * @param list<DiscoveryStateStoreDescriptorDTO> $stores
     * @param list<string>                           $notes
     */
    public function __construct(
        public string $localStateRoot,
        public bool $sharedStateConfigured,
        public bool $distributedReady,
        public array $stores,
        public array $notes = [],
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
            'localStateRoot' => $this->localStateRoot,
            'sharedStateConfigured' => $this->sharedStateConfigured,
            'distributedReady' => $this->distributedReady,
            'stores' => array_map(
                static fn (DiscoveryStateStoreDescriptorDTO $store): array => $store->toArray(),
                $this->stores,
            ),
            'notes' => $this->notes,
        ];
    }
}
