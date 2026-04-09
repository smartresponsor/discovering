<?php

declare(strict_types=1);

namespace App\Service\Discovery\RateLimit;

use App\Dto\Discovery\DiscoveryRateLimitDecision;
use App\Service\Discovery\Http\DiscoveryRequestSurfacePolicy;
use Symfony\Component\HttpFoundation\Request;


/**
 * Provides the discovery rate limiter capability within the discovery component.
 */
final class DiscoveryRateLimiter
{
    public function __construct(
        private readonly DiscoveryRequestSurfacePolicy $surfacePolicy,
        private readonly DiscoveryRateLimitStoreInterface $store,
        private readonly int $queryLimit,
        private readonly int $queryWindowSeconds,
        private readonly int $writeLimit,
        private readonly int $writeWindowSeconds,
        private readonly int $managementMutationLimit,
        private readonly int $managementMutationWindowSeconds,
    ) {
    }

    /**
     * Performs the consume for request operation for this discovery service.
     */
    public function consumeForRequest(Request $request): ?DiscoveryRateLimitDecision
    {
        $scope = $this->surfacePolicy->resolveScope($request);
        if ($scope === null) {
            return null;
        }

        [$limit, $windowSeconds] = match ($scope) {
            'query' => [$this->queryLimit, $this->queryWindowSeconds],
            'write' => [$this->writeLimit, $this->writeWindowSeconds],
            'management_mutation' => [$this->managementMutationLimit, $this->managementMutationWindowSeconds],
            default => throw new \LogicException(sprintf('Unsupported discovery rate limit scope "%s".', $scope)),
        };

        $bucketState = $this->store->increment($scope, $this->actorKey($request, $scope), $windowSeconds);
        $remaining = max(0, $limit - $bucketState['count']);
        $retryAfterSeconds = max(0, $bucketState['resetAt'] - time());

        return new DiscoveryRateLimitDecision(
            scope: $scope,
            limit: $limit,
            remaining: $remaining,
            resetAt: $bucketState['resetAt'],
            retryAfterSeconds: $retryAfterSeconds,
            exceeded: $bucketState['count'] > $limit,
        );
    }

    private function actorKey(Request $request, string $scope): string
    {
        $clientIp = trim((string) ($request->getClientIp() ?? 'unknown'));
        if ($clientIp === '') {
            $clientIp = 'unknown';
        }

        $tokenHeader = match ($scope) {
            'management_mutation' => 'X-Discovery-Management-Token',
            'write' => 'X-Discovery-Api-Write-Token',
            default => '',
        };

        if ($tokenHeader === '') {
            return 'ip:' . $clientIp;
        }

        $token = trim((string) $request->headers->get($tokenHeader, ''));
        if ($token === '') {
            return 'ip:' . $clientIp;
        }

        return 'ip:' . $clientIp . '|token:' . substr(hash('sha256', $token), 0, 16);
    }
}
