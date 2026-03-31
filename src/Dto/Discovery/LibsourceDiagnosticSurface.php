<?php

declare(strict_types=1);

namespace App\Dto\Discovery;

final class LibsourceDiagnosticSurface
{
    /**
     * @param list<LibsourceDiagnosticEntry> $entries
     */
    public function __construct(
        public array $entries,
    ) {
    }
}
