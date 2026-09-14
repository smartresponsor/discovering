<?php

declare(strict_types=1);

namespace App\Discovering\Controller\Management;

use App\Discovering\Service\Discovery\Http\DiscoveryJsonResponseFactory;
use App\Discovering\Service\Discovery\Playbook\PlaybookManagementSurfaceActionResolver;
use App\Discovering\Service\Discovery\Playbook\PlaybookManagementSurfaceBuilder;
use App\Discovering\Service\Discovery\Playbook\PlaybookOperatorEventTrailBuilder;
use App\Discovering\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles management HTTP endpoints for the discovery playbook management surface.
 */
final class DiscoveryPlaybookManagementController extends AbstractDirectoryBackedFamilyManagementController
{
    public function __construct(
        DiscoveryJsonResponseFactory $jsonResponseFactory,
        private readonly PlaybookManagementSurfaceBuilder $surfaceBuilder,
        private readonly PlaybookManagementSurfaceActionResolver $actionResolver,
        private readonly PlaybookOperatorEventTrailBuilder $eventTrailBuilder,
        private readonly PlaybookFileDiscoverySourceRecordRepository $repository,
    ) {
        parent::__construct($jsonResponseFactory);
    }

    /**
     * Handles the index endpoint for the discovery playbook management HTTP surface.
     */
    #[Route('/management/discovery/playbook', name: 'app_management_discovery_playbook', methods: ['GET'])]
    /**
     * @return Response|array<string, mixed>
     */
    public function index(Request $request): Response|array
    {
        $lastActionResult = $this->actionResolver->resolve($request);

        return $this->renderDirectoryBackedFamilyManagement(
            'management/discovery/playbook.html.twig',
            $this->surfaceBuilder->build(),
            $lastActionResult,
            $this->eventTrailBuilder->build($lastActionResult),
        );
    }

    /**
     * Handles the export endpoint for the discovery playbook management HTTP surface.
     */
    #[Route('/management/discovery/playbook/export', name: 'app_management_discovery_playbook_export', methods: ['GET'])]
    public function export(): JsonResponse
    {
        return $this->exportDirectoryBackedFamilySource($this->repository);
    }
}
