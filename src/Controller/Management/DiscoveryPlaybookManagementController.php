<?php

declare(strict_types=1);

namespace App\Discovering\Controller\Management;

use App\Discovering\Builder\Playbook\DiscoveryPlaybookManagementSurfaceBuilder;
use App\Discovering\Builder\Playbook\DiscoveryPlaybookOperatorEventTrailBuilder;
use App\Discovering\Factory\Http\DiscoveryJsonResponseFactory;
use App\Discovering\Repository\Source\DiscoveryPlaybookFileSourceRecordRepository;
use App\Discovering\Resolver\Playbook\DiscoveryPlaybookManagementSurfaceActionResolver;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles management HTTP endpoints for the discovery playbook management surface.
 */
final class DiscoveryPlaybookManagementController extends DiscoveryAbstractDirectoryBackedFamilyManagementController
{
    public function __construct(
        DiscoveryJsonResponseFactory $jsonResponseFactory,
        private readonly DiscoveryPlaybookManagementSurfaceBuilder $surfaceBuilder,
        private readonly DiscoveryPlaybookManagementSurfaceActionResolver $actionResolver,
        private readonly DiscoveryPlaybookOperatorEventTrailBuilder $eventTrailBuilder,
        private readonly DiscoveryPlaybookFileSourceRecordRepository $repository,
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
