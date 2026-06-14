<?php

declare(strict_types=1);

namespace App\RepositoryInterface\Discovery;

use App\Entity\Discovery\DiscoveryIndexDocumentEntity;

interface DiscoveryIndexDocumentRepositoryInterface
{
    public function save(DiscoveryIndexDocumentEntity $entity, bool $flush = false): void;

    public function remove(DiscoveryIndexDocumentEntity $entity, bool $flush = false): void;
}
