<?php

declare(strict_types=1);

namespace App\Discovering\DTO;

/**
 * Represents the libsource diagnostic surface contract used by discovery application, management, or state coordination flows.
 */
final readonly class DiscoveryLibsourceDiagnosticSurfaceDTO
{
    /**
     * @param list<DiscoveryLibsourceDiagnosticEntryDTO> $entries
     */
    public function __construct(
        public array $entries,
    ) {
    }
}
