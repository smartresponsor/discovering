<?php

declare(strict_types=1);

namespace App\Discovering\RepositoryInterface\Discovery;

use App\Discovering\Entity\Discovery\LibsourceOperatorEventEntity;

interface LibsourceOperatorEventRepositoryInterface
{
    public function save(LibsourceOperatorEventEntity $entity, bool $flush = false): void;

    public function remove(LibsourceOperatorEventEntity $entity, bool $flush = false): void;
}
