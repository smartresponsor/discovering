<?php

declare(strict_types=1);

namespace App\Controller\Discovery;

use App\Dto\Discovery\DiscoveryHit;
use App\Service\Discovery\Query\DiscoveryQueryFactory;
use App\ServiceInterface\Discovery\DiscoveryServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class DiscoveryApiController extends AbstractController
{
    public function __construct(
        private readonly DiscoveryServiceInterface $discoveryService,
        private readonly DiscoveryQueryFactory $queryFactory,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $query = $this->queryFactory->fromRequest($request);
        $result = $this->discoveryService->discover($query);

        return $this->json([
            'query' => [
                'term' => $query->term,
                'resourceType' => $query->resourceType,
                'status' => $query->status,
                'visibility' => $query->visibility,
                'limit' => $query->limit,
                'offset' => $query->offset,
            ],
            'total' => $result->total,
            'hits' => array_map(
                static fn (DiscoveryHit $hit): array => [
                    'resourceType' => $hit->resourceType,
                    'resourceId' => $hit->resourceId,
                    'title' => $hit->title,
                    'snippet' => $hit->snippet,
                    'score' => $hit->score,
                    'metadata' => $hit->metadata,
                ],
                $result->hits,
            ),
        ]);
    }
}
