<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

use App\ValueObject\Discovery\DiscoveryDocument;


/**
 * Represents the discovery overview contract used by discovery application, management, or state coordination flows.
 */
final class DiscoveryOverview
{
    /**
     * @param array<string, int> $countsByResourceType
     * @param array<string, int> $countsBySourceName
     * @param list<DiscoveryDocument> $sampleDocuments
     */
    public function __construct(
        public string $backendName,
        public int $totalDocuments,
        public array $countsByResourceType,
        public array $countsBySourceName,
        public array $sampleDocuments,
    ) {
    }
}
