<?php

declare(strict_types=1);

namespace App\ServiceInterface\Discovery\Document;

use App\ValueObject\Discovery\DiscoveryDocument;

interface DiscoveryDocumentProviderInterface
{
    /**
     * @return list<DiscoveryDocument>
     */
    public function provide(): array;
}
