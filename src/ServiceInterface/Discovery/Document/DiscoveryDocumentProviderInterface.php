<?php

declare(strict_types=1);

namespace App\ServiceInterface\Discovery\Document;

use App\ValueObject\Discovery\DiscoveryDocument;


/**
 * Defines the contract for the discovery document provider capability within the discovery component.
 */
interface DiscoveryDocumentProviderInterface
{
    /**
     * @return list<DiscoveryDocument>
     */
    public function provide(): array;
}
