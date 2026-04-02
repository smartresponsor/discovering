<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

final class LibsourceDiagnosticEntry
{
    /**
     * @param list<string> $sampleResourceIds
     */
    public function __construct(
        public string $sourceName,
        public string $resourceType,
        public string $providerClass,
        public string $repositoryClass,
        public int $recordCount,
        public array $sampleResourceIds,
    ) {
    }
}
