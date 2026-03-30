<?php

declare(strict_types=1);

namespace App\Controller\Management;

use App\ServiceInterface\Discovery\Overview\DiscoveryOverviewServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class DiscoveryManagementController extends AbstractController
{
    public function __construct(
        private readonly DiscoveryOverviewServiceInterface $overviewService,
    ) {
    }

    public function overview(): Response
    {
        return $this->render('management/discovery/overview.html.twig', [
            'overview' => $this->overviewService->buildOverview(),
        ]);
    }
}
