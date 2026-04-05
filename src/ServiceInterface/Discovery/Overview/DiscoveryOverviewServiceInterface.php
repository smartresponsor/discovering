<?php

declare(strict_types=1);

namespace App\ServiceInterface\Discovery\Overview;

use App\Dto\Discovery\DiscoveryOverview;

interface DiscoveryOverviewServiceInterface
{
    public function buildOverview(): DiscoveryOverview;
}
