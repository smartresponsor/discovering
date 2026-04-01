<?php

declare(strict_types=1);

namespace App\Controller\Management;

use App\Service\Discovery\Briefing\BriefingManagementSurfaceBuilder;
use App\Service\Discovery\Source\Repository\BriefingFileDiscoverySourceRecordRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DiscoveryBriefingManagementController extends AbstractController
{
    public function __construct(
        private readonly BriefingManagementSurfaceBuilder $surfaceBuilder,
        private readonly BriefingFileDiscoverySourceRecordRepository $repository,
    ) {
    }

    #[Route('/management/discovery/briefing', name: 'app_management_discovery_briefing', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('management/discovery/briefing.html.twig', [
            'surface' => $this->surfaceBuilder->build(),
        ]);
    }

    #[Route('/management/discovery/briefing/export', name: 'app_management_discovery_briefing_export', methods: ['GET'])]
    public function export(): JsonResponse
    {
        return $this->json([
            'sourceName' => $this->repository->getSourceName(),
            'storagePath' => $this->repository->getStoragePath(),
            'records' => json_decode($this->repository->exportJson(), true),
        ]);
    }
}
