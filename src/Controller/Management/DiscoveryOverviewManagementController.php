<?php

declare(strict_types=1);

namespace App\Discovering\Controller\Management;

use App\Discovering\Service\Discovery\Diagnostics\DiscoveryBackendReachabilityBuilder;
use App\Discovering\Service\Discovery\Diagnostics\DiscoveryPlatformDiagnosticsBuilder;
use App\Discovering\Service\Discovery\Http\DiscoveryJsonResponseFactory;
use App\Discovering\Service\Discovery\Operations\DiscoveryOperationLogger;
use App\Discovering\Service\Discovery\Rebuild\DiscoveryRollbackPlanBuilder;
use App\Discovering\Service\Discovery\Rollback\DiscoveryRollbackExecutor;
use App\Discovering\Service\Discovery\Topology\DiscoveryStateTopologyBuilder;
use App\Discovering\ServiceInterface\Discovery\Operations\DiscoveryOperationEventLogStoreInterface;
use App\Discovering\ServiceInterface\Discovery\Overview\DiscoveryOverviewServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles management HTTP endpoints for the discovery overview management surface.
 */
final class DiscoveryOverviewManagementController
{
    public function __construct(
        private readonly DiscoveryOverviewServiceInterface $overviewService,
        private readonly DiscoveryOperationEventLogStoreInterface $operationLogStore,
        private readonly DiscoveryOperationLogger $operationLogger,
        private readonly DiscoveryStateTopologyBuilder $stateTopologyBuilder,
        private readonly DiscoveryPlatformDiagnosticsBuilder $platformDiagnosticsBuilder,
        private readonly DiscoveryBackendReachabilityBuilder $backendReachabilityBuilder,
        private readonly DiscoveryRollbackPlanBuilder $rollbackPlanBuilder,
        private readonly DiscoveryRollbackExecutor $rollbackExecutor,
        private readonly DiscoveryJsonResponseFactory $jsonResponseFactory,
    ) {
    }

    /**
     * Handles the index endpoint for the discovery overview management HTTP surface.
     */
    #[Route('/management/discovery', name: 'app_management_discovery_overview', methods: ['GET'])]
    /**
     * @return Response|array<string, mixed>
     */
    public function index(): Response|array
    {
        $overview = $this->overviewService->buildOverview();
        $operations = $this->operationLogStore->latest(10);
        $stateTopology = $this->stateTopologyBuilder->build();
        $platformDiagnostics = $this->platformDiagnosticsBuilder->build();
        $rollbackPlan = $this->rollbackPlanBuilder->build();
        $this->operationLogger->recordHttp('discovery.management.overview');

        return [
            '_view' => [
                'surface' => 'discovery',
                'operation' => 'overview',
                'component' => 'Discovering',
                'intent' => 'management',
            ],
            'data' => [
                'templateName' => '@Discovering/management/discovery/overview.html.twig',
                'overview' => $overview,
                'operations' => $operations,
                'stateTopology' => $stateTopology,
                'platformDiagnostics' => $platformDiagnostics,
                'rollbackPlan' => $rollbackPlan,
            ],
            'meta' => [
                'source_controller' => self::class,
                'legacy_template' => 'management/discovery/overview.html.twig',
            ],
        ];
    }

    /**
     * Handles the export endpoint for the discovery overview management HTTP surface.
     */
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

    /**
     * Handles the executeRollback endpoint for the discovery overview management HTTP surface.
     */
    #[Route('/management/discovery/rollback/execute', name: 'app_management_discovery_rollback_execute', methods: ['POST'])]
    public function executeRollback(Request $request): JsonResponse
    {
        $currentEvidenceId = trim((string) $request->request->get('current', $request->query->get('current', '')));
        $targetEvidenceId = trim((string) $request->request->get('target', $request->query->get('target', '')));

        $result = $this->rollbackExecutor->execute(
            expectedCurrentEvidenceId: '' === $currentEvidenceId ? null : $currentEvidenceId,
            expectedTargetEvidenceId: '' === $targetEvidenceId ? null : $targetEvidenceId,
        );

        $this->operationLogger->recordHttp(
            'discovery.management.rollback.execute',
            status: $result->executed ? 'ok' : 'blocked',
            context: $result->toArray(),
        );

        if (!$result->executed) {
            return $this->jsonResponseFactory->error(
                'discovery_rollback_blocked',
                'Discovery rollback execution was blocked by the current rollback posture.',
                JsonResponse::HTTP_CONFLICT,
                $result->toArray(),
                [
                    'schemaFamily' => 'discovery.rollback.execution',
                    'schemaVersion' => 1,
                ],
            );
        }

        return $this->jsonResponseFactory->success($result->toArray(), [
            'schemaFamily' => 'discovery.rollback.execution',
            'schemaVersion' => 1,
        ]);
    }

    /**
     * Handles the exportRollbackPlan endpoint for the discovery overview management HTTP surface.
     */
    #[Route('/management/discovery/rollback/export', name: 'app_management_discovery_rollback_export', methods: ['GET'])]
    public function exportRollbackPlan(): JsonResponse
    {
        $this->operationLogger->recordHttp('discovery.management.rollback.export');

        return $this->jsonResponseFactory->success($this->rollbackPlanBuilder->build()->toArray(), [
            'schemaFamily' => 'discovery.rollback.plan',
            'schemaVersion' => 1,
        ]);
    }

    /**
     * Handles the exportPlatformDiagnostics endpoint for the discovery overview management HTTP surface.
     */
    #[Route('/management/discovery/platform/export', name: 'app_management_discovery_platform_export', methods: ['GET'])]
    public function exportPlatformDiagnostics(): JsonResponse
    {
        $this->operationLogger->recordHttp('discovery.management.platform.export');

        return $this->jsonResponseFactory->success($this->platformDiagnosticsBuilder->build()->toArray(), [
            'schemaFamily' => 'discovery.platform.diagnostics',
            'schemaVersion' => 1,
        ]);
    }

    /**
     * Handles the exportPlatformProbes endpoint for the discovery overview management HTTP surface.
     */
    #[Route('/management/discovery/platform/probes/export', name: 'app_management_discovery_platform_probes_export', methods: ['GET'])]
    public function exportPlatformProbes(): JsonResponse
    {
        $this->operationLogger->recordHttp('discovery.management.platform.probes.export');

        return $this->jsonResponseFactory->success($this->backendReachabilityBuilder->build()->toArray(), [
            'schemaFamily' => 'discovery.platform.probes',
            'schemaVersion' => 1,
        ]);
    }

    /**
     * Handles the exportOperations endpoint for the discovery overview management HTTP surface.
     */
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

    /**
     * Handles the exportStateTopology endpoint for the discovery overview management HTTP surface.
     */
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
