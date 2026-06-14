<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

/**
 * Represents the libsource diagnostic surface contract used by discovery application, management, or state coordination flows.
 */
final readonly class LibsourceDiagnosticSurface
{
    /**
     * @param list<LibsourceDiagnosticEntry> $entries
     */
    public function __construct(
        public array $entries,
    ) {
    }
}
