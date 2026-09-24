<?php

declare(strict_types=1);

namespace App\Discovering\Controller\Management;

use App\Discovering\Builder\Briefing\DiscoveryBriefingManagementSurfaceBuilder;
use App\Discovering\Builder\Briefing\DiscoveryBriefingOperatorEventTrailBuilder;
use App\Discovering\Factory\Http\DiscoveryJsonResponseFactory;
use App\Discovering\Repository\Source\DiscoveryBriefingFileSourceRecordRepository;
use App\Discovering\Resolver\Briefing\DiscoveryBriefingManagementSurfaceActionResolver;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles management HTTP endpoints for the discovery briefing management surface.
 */
final class DiscoveryBriefingManagementController extends DiscoveryAbstractDirectoryBackedFamilyManagementController
{
    public function __construct(
        DiscoveryJsonResponseFactory $jsonResponseFactory,
        private readonly DiscoveryBriefingManagementSurfaceBuilder $surfaceBuilder,
        private readonly DiscoveryBriefingManagementSurfaceActionResolver $actionResolver,
        private readonly DiscoveryBriefingOperatorEventTrailBuilder $eventTrailBuilder,
        private readonly DiscoveryBriefingFileSourceRecordRepository $repository,
    ) {
        parent::__construct($jsonResponseFactory);
    }

    /**
     * Handles the index endpoint for the discovery briefing management HTTP surface.
     */
    #[Route('/management/discovery/briefing', name: 'app_management_discovery_briefing', methods: ['GET'])]
    /**
     * @return Response|array<string, mixed>
     */
    public function index(Request $request): Response|array
    {
        $lastActionResult = $this->actionResolver->resolve($request);

        return $this->renderDirectoryBackedFamilyManagement(
            'management/discovery/briefing.html.twig',
            $this->surfaceBuilder->build(),
            $lastActionResult,
            $this->eventTrailBuilder->build($lastActionResult),
        );
    }

    /**
     * Handles the export endpoint for the discovery briefing management HTTP surface.
     */
    #[Route('/management/discovery/briefing/export', name: 'app_management_discovery_briefing_export', methods: ['GET'])]
    public function export(): JsonResponse
    {
        return $this->exportDirectoryBackedFamilySource($this->repository);
    }
}
