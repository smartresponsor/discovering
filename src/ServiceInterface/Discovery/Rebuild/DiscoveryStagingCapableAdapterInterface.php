<?php

declare(strict_types=1);

namespace App\ServiceInterface\Discovery\Rebuild;

interface DiscoveryStagingCapableAdapterInterface
{
    public function supportsStagedRebuild(): bool;
}
