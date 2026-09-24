<?php

declare(strict_types=1);

namespace App\Discovering\Service\RateLimit;

use App\Discovering\DTO\DiscoveryRateLimitDecisionDTO;
use App\Discovering\ServiceInterface\RateLimit\DiscoveryRateLimitStoreInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the discovery rate limiter capability within the discovery component.
 */
final class DiscoveryRateLimiter
{
    public function __construct(
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
    public function consumeForRequest(Request $request): ?DiscoveryRateLimitDecisionDTO
    {
        $scope = $this->resolveScope($request);
        if (null === $scope) {
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

        return new DiscoveryRateLimitDecisionDTO(
            scope: $scope,
            limit: $limit,
            remaining: $remaining,
            resetAt: $bucketState['resetAt'],
            retryAfterSeconds: $retryAfterSeconds,
            exceeded: $bucketState['count'] > $limit,
        );
    }

    private function resolveScope(Request $request): ?string
    {
        $path = $request->getPathInfo();

        if ($this->isWritePath($request, $path)) {
            return 'write';
        }

        if ($this->isManagementMutationPath($request, $path)) {
            return 'management_mutation';
        }

        if ($this->isQueryPath($request, $path)) {
            return 'query';
        }

        return null;
    }

    private function isQueryPath(Request $request, string $path): bool
    {
        if ($request->isMethod(Request::METHOD_GET) && ('/api/v1/discovery' === $path || '/api/discovery' === $path)) {
            return true;
        }

        return ($request->isMethod(Request::METHOD_GET) || $request->isMethod(Request::METHOD_POST))
            && '/discovery' === $path;
    }

    private function isWritePath(Request $request, string $path): bool
    {
        return $request->isMethod(Request::METHOD_POST)
            && ('/discovery/feedback' === $path || '/api/v1/discovery/click' === $path || '/api/discovery/click' === $path);
    }

    private function isManagementMutationPath(Request $request, string $path): bool
    {
        if (!str_starts_with($path, '/management/discovery')) {
            return false;
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            return true;
        }

        $action = $request->query->get('action');

        return is_string($action) && '' !== trim($action);
    }

    private function actorKey(Request $request, string $scope): string
    {
        $clientIp = trim((string) ($request->getClientIp() ?? 'unknown'));
        if ('' === $clientIp) {
            $clientIp = 'unknown';
        }

        $tokenHeader = match ($scope) {
            'management_mutation' => 'X-Discovery-Management-Token',
            'write' => 'X-Discovery-Api-Write-Token',
            default => '',
        };

        if ('' === $tokenHeader) {
            return 'ip:'.$clientIp;
        }

        $token = trim((string) $request->headers->get($tokenHeader, ''));
        if ('' === $token) {
            return 'ip:'.$clientIp;
        }

        return 'ip:'.$clientIp.'|token:'.substr(hash('sha256', $token), 0, 16);
    }
}
