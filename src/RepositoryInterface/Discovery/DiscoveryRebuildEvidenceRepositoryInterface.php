<?php

declare(strict_types=1);

namespace App\RepositoryInterface\Discovery;

use App\Entity\Discovery\DiscoveryRebuildEvidenceEntity;

interface DiscoveryRebuildEvidenceRepositoryInterface
{
    public function save(DiscoveryRebuildEvidenceEntity $entity, bool $flush = false): void;

    public function remove(DiscoveryRebuildEvidenceEntity $entity, bool $flush = false): void;
}
