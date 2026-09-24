<?php

declare(strict_types=1);

namespace App\Discovering\RepositoryInterface;

use App\Discovering\Entity\DiscoveryIndexDocumentEntity;

/**
 * Defines persistence operations for indexed discovery documents.
 */
interface DiscoveryIndexDocumentRepositoryInterface
{
    public function save(DiscoveryIndexDocumentEntity $entity, bool $flush = false): void;

    public function remove(DiscoveryIndexDocumentEntity $entity, bool $flush = false): void;
}
