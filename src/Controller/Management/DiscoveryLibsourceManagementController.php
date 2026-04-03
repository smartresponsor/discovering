<?php

declare(strict_types=1);

namespace App\Controller\Management;

use App\Service\Discovery\Http\DiscoveryJsonResponseFactory;
use App\Service\Discovery\Libsource\LibsourceDiagnosticSurfaceBuilder;
use App\Service\Discovery\Libsource\LibsourceManagementSurfaceActionResolver;
use App\Service\Discovery\Libsource\LibsourceOperatorEventTrailBuilder;
use App\Service\Discovery\Source\Repository\DiscoverySourceRepositoryRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DiscoveryLibsourceManagementController extends AbstractController
{
    public function __construct(
        private readonly LibsourceDiagnosticSurfaceBuilder $diagnosticSurfaceBuilder,
        private readonly LibsourceManagementSurfaceActionResolver $actionResolver,
        private readonly LibsourceOperatorEventTrailBuilder $eventTrailBuilder,
        private readonly DiscoverySourceRepositoryRegistry $repositoryRegistry,
        private readonly DiscoveryJsonResponseFactory $jsonResponseFactory,
    ) {
    }

    #[Route('/management/discovery/libsource', name: 'app_management_discovery_libsource', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $lastActionResult = $this->actionResolver->resolve($request);

        return $this->render('management/discovery/libsource.html.twig', [
            'surface' => $this->diagnosticSurfaceBuilder->build(),
            'lastActionResult' => $lastActionResult,
            'operatorEventTrail' => $this->eventTrailBuilder->build($lastActionResult),
        ]);
    }

    #[Route('/management/discovery/libsource/inspect/{sourceName}', name: 'app_management_discovery_libsource_inspect', methods: ['GET'])]
    public function inspect(string $sourceName): JsonResponse
    {
        $repository = $this->repositoryRegistry->getBySourceName($sourceName);

        $records = method_exists($repository, 'exportJson')
            ? json_decode($repository->exportJson(), true)
            : array_map(
                static fn ($record): array => [
                    'resourceType' => $record->resourceType,
                    'resourceId' => $record->resourceId,
                    'title' => $record->title,
                    'body' => $record->body,
                    'filters' => $record->filters,
                    'metadata' => $record->metadata,
                ],
                $repository->all(),
            );

        return $this->jsonResponseFactory->success([
            'sourceName' => $repository->getSourceName(),
            'resourceType' => $repository->getResourceType(),
            'storagePath' => method_exists($repository, 'getStoragePath') ? $repository->getStoragePath() : null,
            'records' => $records,
        ]);
    }
}
