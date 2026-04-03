<?php

declare(strict_types=1);

namespace App\Controller\Management;

use App\Dto\Discovery\ReindexRequest;
use App\Service\Discovery\Http\DiscoveryJsonResponseFactory;
use App\ServiceInterface\Discovery\Indexer\DiscoveryIndexerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class DiscoveryManagementController extends AbstractController
{
    public function __construct(
        private readonly DiscoveryIndexerInterface $discoveryIndexer,
        private readonly DiscoveryJsonResponseFactory $jsonResponseFactory,
    ) {
    }

    #[Route('/management/discovery/rebuild', name: 'app_management_discovery_rebuild', methods: ['POST'])]
    public function rebuild(): JsonResponse
    {
        $this->discoveryIndexer->rebuild(new ReindexRequest(resource: 'global', rebuildMode: 'full'));

        return $this->jsonResponseFactory->success([
            'message' => 'Discovery rebuild triggered.',
            'resource' => 'global',
            'rebuildMode' => 'full',
        ]);
    }
}
