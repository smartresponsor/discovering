<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

/**
 * Represents the discovery state topology contract used by discovery application, management, or state coordination flows.
 */
final readonly class DiscoveryStateTopology
{
    /**
     * @param list<DiscoveryStateStoreDescriptor> $stores
     * @param list<string>                        $notes
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
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'localStateRoot' => $this->localStateRoot,
            'sharedStateConfigured' => $this->sharedStateConfigured,
            'distributedReady' => $this->distributedReady,
            'stores' => array_map(
                static fn (DiscoveryStateStoreDescriptor $store): array => $store->toArray(),
                $this->stores,
            ),
            'notes' => $this->notes,
        ];
    }
}
