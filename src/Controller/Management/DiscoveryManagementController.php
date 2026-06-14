<?php

declare(strict_types=1);

namespace App\Controller\Management;

use App\Dto\Discovery\ReindexRequest;
use App\Service\Discovery\Http\DiscoveryJsonResponseFactory;
use App\Service\Discovery\Operations\DiscoveryOperationLogger;
use App\ServiceInterface\Discovery\Indexer\DiscoveryIndexerInterface;
use App\ServiceInterface\Discovery\Rebuild\DiscoveryRebuildEvidenceStoreInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles management HTTP endpoints for the discovery management surface.
 */
final class DiscoveryManagementController
{
    public function __construct(
        private readonly DiscoveryIndexerInterface $discoveryIndexer,
        private readonly DiscoveryRebuildEvidenceStoreInterface $rebuildEvidenceStore,
        private readonly DiscoveryOperationLogger $operationLogger,
        private readonly DiscoveryJsonResponseFactory $jsonResponseFactory,
    ) {
    }

    /**
     * Handles the rebuild endpoint for the discovery management HTTP surface.
     */
    #[Route('/management/discovery/rebuild', name: 'app_management_discovery_rebuild', methods: ['POST'])]
    public function rebuild(Request $request): JsonResponse
    {
        $requestedDeploymentMode = trim((string) $request->request->get('deploymentMode', $request->query->get('deploymentMode', 'auto')));
        if ('' === $requestedDeploymentMode) {
            $requestedDeploymentMode = 'auto';
        }

        $summary = $this->discoveryIndexer->rebuild(new ReindexRequest(
            resource: 'global',
            rebuildMode: 'full',
            deploymentMode: $requestedDeploymentMode,
        ));
        $this->rebuildEvidenceStore->append($summary);
        $this->operationLogger->recordHttp('discovery.management.rebuild', context: [
            'evidenceId' => $summary->evidenceId,
            'indexedDocumentCount' => $summary->indexedDocumentCount,
            'candidateDocumentCount' => $summary->candidateDocumentCount,
            'deploymentMode' => $summary->deploymentMode,
            'requestedDeploymentMode' => $requestedDeploymentMode,
            'zeroDowntimeReady' => $summary->zeroDowntimeReady,
            'aliasSwapApplied' => $summary->aliasSwapApplied,
            'stagedIndexes' => $summary->stagedIndexes,
        ]);

        return $this->jsonResponseFactory->success($summary->toArray(), [
            'schemaFamily' => 'discovery.rebuild.summary',
            'schemaVersion' => 1,
        ]);
    }

    /**
     * Handles the exportRebuilds endpoint for the discovery management HTTP surface.
     */
    #[Route('/management/discovery/rebuilds/export', name: 'app_management_discovery_rebuilds_export', methods: ['GET'])]
    public function exportRebuilds(): JsonResponse
    {
        $this->operationLogger->recordHttp('discovery.management.rebuilds.export');

        return $this->jsonResponseFactory->success(array_map(
            static fn ($summary): array => $summary->toArray(),
            $this->rebuildEvidenceStore->latest(25),
        ), [
            'schemaFamily' => 'discovery.rebuild.summary.list',
            'schemaVersion' => 1,
        ]);
    }
}
