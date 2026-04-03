<?php

declare(strict_types=1);

namespace App\Service\Discovery;

final class ConfigurableDiscoveryFeedbackStore implements DiscoveryFeedbackStoreInterface
{
    public function __construct(
        private readonly DiscoveryFeedbackStore $sqliteStore,
        private readonly PdoDiscoveryFeedbackStore $pdoStore,
        private readonly string $backend,
        private readonly string $pdoDsn,
    ) {
    }

    public function recordClick(string $resource, string $hitId, string $title = '', string $reference = ''): int
    {
        return $this->delegate()->recordClick($resource, $hitId, $title, $reference);
    }

    public function getClickCount(string $resource, string $hitId): int
    {
        return $this->delegate()->getClickCount($resource, $hitId);
    }

    private function delegate(): DiscoveryFeedbackStoreInterface
    {
        return match (strtolower(trim($this->backend))) {
            '', 'sqlite_path' => $this->sqliteStore,
            'pdo' => trim($this->pdoDsn) !== '' ? $this->pdoStore : $this->sqliteStore,
            default => throw new \RuntimeException(sprintf('Unsupported discovery feedback backend "%s". Expected "sqlite_path" or "pdo".', $this->backend)),
        };
    }
}
