<?php

declare(strict_types=1);

namespace App\RepositoryInterface\Discovery;

use App\Entity\Discovery\LibsourceOperatorEventEntity;

interface LibsourceOperatorEventRepositoryInterface
{
    public function save(LibsourceOperatorEventEntity $entity, bool $flush = false): void;

    public function remove(LibsourceOperatorEventEntity $entity, bool $flush = false): void;
}
