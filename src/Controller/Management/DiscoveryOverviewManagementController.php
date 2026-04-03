<?php

declare(strict_types=1);

namespace App\Controller\Management;

use App\Service\Discovery\Operations\DiscoveryOperationEventLogStoreInterface;
use App\Service\Discovery\Operations\DiscoveryOperationLogger;
use App\ServiceInterface\Discovery\Overview\DiscoveryOverviewServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DiscoveryOverviewManagementController extends AbstractController
{
    public function __construct(
        private readonly DiscoveryOverviewServiceInterface $overviewService,
        private readonly DiscoveryOperationEventLogStoreInterface $operationLogStore,
        private readonly DiscoveryOperationLogger $operationLogger,
    ) {
    }

    #[Route('/management/discovery', name: 'app_management_discovery_overview', methods: ['GET'])]
    public function index(): Response
    {
        $overview = $this->overviewService->buildOverview();
        $operations = $this->operationLogStore->latest(10);
        $this->operationLogger->recordHttp('discovery.management.overview');

        return $this->render('management/discovery/overview.html.twig', [
            'overview' => $overview,
            'operations' => $operations,
        ]);
    }

    #[Route('/management/discovery/export', name: 'app_management_discovery_overview_export', methods: ['GET'])]
    public function export(): JsonResponse
    {
        $overview = $this->overviewService->buildOverview();
        $this->operationLogger->recordHttp('discovery.management.overview.export');

        return $this->json([
            'backendName' => $overview->backendName,
            'totalDocuments' => $overview->totalDocuments,
            'countsByResourceType' => $overview->countsByResourceType,
            'countsBySourceName' => $overview->countsBySourceName,
            'sampleDocuments' => array_map(
                static fn ($document): array => $document->toArray(),
                $overview->sampleDocuments,
            ),
            'operations' => array_map(
                static fn ($event): array => [
                    'requestId' => $event->requestId,
                    'channel' => $event->channel,
                    'operation' => $event->operation,
                    'status' => $event->status,
                    'occurredAt' => $event->occurredAt,
                    'context' => $event->context,
                ],
                $this->operationLogStore->latest(25),
            ),
        ]);
    }

    #[Route('/management/discovery/operations/export', name: 'app_management_discovery_operations_export', methods: ['GET'])]
    public function exportOperations(): JsonResponse
    {
        $this->operationLogger->recordHttp('discovery.management.operations.export');

        return $this->json([
            'ok' => true,
            'data' => array_map(
                static fn ($event): array => [
                    'requestId' => $event->requestId,
                    'channel' => $event->channel,
                    'operation' => $event->operation,
                    'status' => $event->status,
                    'occurredAt' => $event->occurredAt,
                    'context' => $event->context,
                ],
                $this->operationLogStore->latest(50),
            ),
        ]);
    }
}
