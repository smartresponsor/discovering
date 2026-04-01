<?php

declare(strict_types=1);

namespace App\Controller\Management;

use App\Service\Discovery\Playbook\PlaybookManagementSurfaceActionResolver;
use App\Service\Discovery\Playbook\PlaybookManagementSurfaceBuilder;
use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DiscoveryPlaybookManagementController extends AbstractController
{
    public function __construct(
        private readonly PlaybookManagementSurfaceBuilder $surfaceBuilder,
        private readonly PlaybookManagementSurfaceActionResolver $actionResolver,
        private readonly PlaybookFileDiscoverySourceRecordRepository $repository,
    ) {
    }

    #[Route('/management/discovery/playbook', name: 'app_management_discovery_playbook', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $lastActionResult = $this->actionResolver->resolve($request);

        return $this->render('management/discovery/playbook.html.twig', [
            'surface' => $this->surfaceBuilder->build(),
            'lastActionResult' => $lastActionResult,
        ]);
    }

    #[Route('/management/discovery/playbook/export', name: 'app_management_discovery_playbook_export', methods: ['GET'])]
    public function export(): JsonResponse
    {
        return $this->json([
            'sourceName' => $this->repository->getSourceName(),
            'storagePath' => $this->repository->getStoragePath(),
            'records' => json_decode($this->repository->exportJson(), true),
        ]);
    }
}
