<?php

declare(strict_types=1);

namespace App\Service\Discovery\Libsource\Log;

use App\Dto\Discovery\LibsourceOperatorEvent;

interface LibsourceOperatorEventLogStoreInterface
{
    public function append(LibsourceOperatorEvent $event): void;

    /**
     * @return list<LibsourceOperatorEvent>
     */
    public function all(): array;

    public function clear(): void;
}
