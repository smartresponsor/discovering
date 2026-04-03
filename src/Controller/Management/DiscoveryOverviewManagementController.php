<?php

declare(strict_types=1);

namespace App\Controller\Management;

use App\Service\Discovery\Http\DiscoveryJsonResponseFactory;
use App\Service\Discovery\Operations\DiscoveryOperationEventLogStoreInterface;
use App\Service\Discovery\Operations\DiscoveryOperationLogger;
use App\Service\Discovery\Topology\DiscoveryStateTopologyBuilder;
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
        private readonly DiscoveryStateTopologyBuilder $stateTopologyBuilder,
        private readonly DiscoveryJsonResponseFactory $jsonResponseFactory,
    ) {
    }

    #[Route('/management/discovery', name: 'app_management_discovery_overview', methods: ['GET'])]
    public function index(): Response
    {
        $overview = $this->overviewService->buildOverview();
        $operations = $this->operationLogStore->latest(10);
        $stateTopology = $this->stateTopologyBuilder->build();
        $this->operationLogger->recordHttp('discovery.management.overview');

        return $this->render('management/discovery/overview.html.twig', [
            'overview' => $overview,
            'operations' => $operations,
            'stateTopology' => $stateTopology,
        ]);
    }

    #[Route('/management/discovery/export', name: 'app_management_discovery_overview_export', methods: ['GET'])]
    public function export(): JsonResponse
    {
        $overview = $this->overviewService->buildOverview();
        $this->operationLogger->recordHttp('discovery.management.overview.export');

        return $this->jsonResponseFactory->success([
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

        return $this->jsonResponseFactory->success(array_map(
            static fn ($event): array => [
                'requestId' => $event->requestId,
                'channel' => $event->channel,
                'operation' => $event->operation,
                'status' => $event->status,
                'occurredAt' => $event->occurredAt,
                'context' => $event->context,
            ],
            $this->operationLogStore->latest(50),
        ));
    }

    #[Route('/management/discovery/state-topology/export', name: 'app_management_discovery_state_topology_export', methods: ['GET'])]
    public function exportStateTopology(): JsonResponse
    {
        $this->operationLogger->recordHttp('discovery.management.state_topology.export');

        return $this->jsonResponseFactory->success($this->stateTopologyBuilder->build()->toArray(), [
            'schemaFamily' => 'discovery.state.topology',
            'schemaVersion' => 1,
        ]);
    }
}
