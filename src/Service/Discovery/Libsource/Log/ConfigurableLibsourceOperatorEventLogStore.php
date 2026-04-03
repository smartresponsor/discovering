<?php

declare(strict_types=1);

namespace App\Service\Discovery\Libsource\Log;

use App\Dto\Discovery\LibsourceOperatorEvent;

final class ConfigurableLibsourceOperatorEventLogStore implements LibsourceOperatorEventLogStoreInterface
{
    public function __construct(
        private readonly FileLibsourceOperatorEventLogStore $fileStore,
        private readonly PdoLibsourceOperatorEventLogStore $pdoStore,
        private readonly string $backend,
        private readonly string $pdoDsn,
    ) {
    }

    public function append(LibsourceOperatorEvent $event): void
    {
        $this->delegate()->append($event);
    }

    public function all(): array
    {
        return $this->delegate()->all();
    }

    public function clear(): void
    {
        $this->delegate()->clear();
    }

    private function delegate(): LibsourceOperatorEventLogStoreInterface
    {
        return match (strtolower(trim($this->backend))) {
            '', 'file' => $this->fileStore,
            'pdo' => trim($this->pdoDsn) !== '' ? $this->pdoStore : $this->fileStore,
            default => throw new \RuntimeException(sprintf('Unsupported libsource operator event log backend "%s". Expected "file" or "pdo".', $this->backend)),
        };
    }
}
