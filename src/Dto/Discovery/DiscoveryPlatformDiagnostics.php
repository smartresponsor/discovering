<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

final class DiscoveryPlatformDiagnostics
{
    /**
     * @param array<string, string> $storeBackends
     * @param list<string> $multiReplicaWriteReadyStores
     * @param list<string> $blockingStores
     * @param list<string> $notes
     */
    public function __construct(
        public string $backendName,
        public string $indexStoreBackend,
        public bool $stagedRebuildSupported,
        public bool $sharedStateConfigured,
        public bool $distributedReady,
        public string $rollbackStatus,
        public bool $rollbackReady,
        public int $coordinationReadyStoreCount,
        public array $storeBackends,
        public array $multiReplicaWriteReadyStores = [],
        public array $blockingStores = [],
        public array $notes = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'backendName' => $this->backendName,
            'indexStoreBackend' => $this->indexStoreBackend,
            'stagedRebuildSupported' => $this->stagedRebuildSupported,
            'sharedStateConfigured' => $this->sharedStateConfigured,
            'distributedReady' => $this->distributedReady,
            'rollbackStatus' => $this->rollbackStatus,
            'rollbackReady' => $this->rollbackReady,
            'coordinationReadyStoreCount' => $this->coordinationReadyStoreCount,
            'storeBackends' => $this->storeBackends,
            'multiReplicaWriteReadyStores' => $this->multiReplicaWriteReadyStores,
            'blockingStores' => $this->blockingStores,
            'notes' => $this->notes,
        ];
    }
}
