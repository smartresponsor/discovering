<?php

declare(strict_types=1);

namespace App\Discovering\DTO;

/**
 * Represents the libsource diagnostic entry contract used by discovery application, management, or state coordination flows.
 */
final readonly class DiscoveryLibsourceDiagnosticEntryDTO
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
