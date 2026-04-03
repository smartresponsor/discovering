<?php

declare(strict_types=1);

namespace App\Controller\Management;

use App\ServiceInterface\Discovery\Overview\DiscoveryOverviewServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DiscoveryOverviewManagementController extends AbstractController
{
    public function __construct(
        private readonly DiscoveryOverviewServiceInterface $overviewService,
    ) {
    }

    #[Route('/management/discovery', name: 'app_management_discovery_overview', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('management/discovery/overview.html.twig', [
            'overview' => $this->overviewService->buildOverview(),
        ]);
    }

    #[Route('/management/discovery/export', name: 'app_management_discovery_overview_export', methods: ['GET'])]
    public function export(): JsonResponse
    {
        $overview = $this->overviewService->buildOverview();

        return $this->json([
            'backendName' => $overview->backendName,
            'totalDocuments' => $overview->totalDocuments,
            'countsByResourceType' => $overview->countsByResourceType,
            'countsBySourceName' => $overview->countsBySourceName,
            'sampleDocuments' => array_map(
                static fn ($document): array => $document->toArray(),
                $overview->sampleDocuments,
            ),
        ]);
    }
}
