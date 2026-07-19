<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Http;

use App\Discovering\Service\Discovery\Operations\DiscoveryOperationLogger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides the discovery json response factory capability within the discovery component.
 */
final class DiscoveryJsonResponseFactory
{
    public const string API_VERSION = DiscoveryApiContract::API_VERSION;
    public const string API_VERSION_HEADER = DiscoveryApiContract::API_VERSION_HEADER;
    public const string SCHEMA_FAMILY_HEADER = DiscoveryApiContract::SCHEMA_FAMILY_HEADER;
    public const string SCHEMA_VERSION_HEADER = DiscoveryApiContract::SCHEMA_VERSION_HEADER;

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function success(mixed $data, array $meta = [], int $status = JsonResponse::HTTP_OK): JsonResponse
    {
        $response = new JsonResponse([
            'ok' => true,
            'apiVersion' => self::API_VERSION,
            'requestId' => $this->resolveRequestId(),
            'meta' => $this->buildMeta($meta),
            'data' => $data,
        ], $status);

        return $this->applyHeaders($response);
    }

    /**
     * @param array<string, mixed> $details
     * @param array<string, mixed> $meta
     */
    public function error(
        string $code,
        string $message,
        int $status,
        array $details = [],
        array $meta = [],
    ): JsonResponse {
        $response = new JsonResponse([
            'ok' => false,
            'apiVersion' => self::API_VERSION,
            'requestId' => $this->resolveRequestId(),
            'meta' => $this->buildMeta($meta),
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
        ], $status);

        return $this->applyHeaders($response);
    }

    /**
     * @param array<string, mixed> $meta
     *
     * @return array<string, mixed>
     */
    private function buildMeta(array $meta): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $path = $request instanceof Request ? $request->getPathInfo() : null;
        $canonicalPath = null !== $path ? $this->canonicalPath($path) : null;

        $descriptiveMeta = array_replace([
            'schemaFamily' => DiscoveryApiContract::ENVELOPE_SCHEMA_FAMILY,
            'schemaVersion' => DiscoveryApiContract::ENVELOPE_SCHEMA_VERSION,
        ], $meta);

        return [
            'canonicalPath' => $canonicalPath,
            'deprecatedAlias' => null !== $path && null !== $canonicalPath && $canonicalPath !== $path,
        ] + $descriptiveMeta;
    }

    private function resolveRequestId(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return null;
        }

        $requestId = $request->attributes->get(DiscoveryOperationLogger::REQUEST_ID_ATTRIBUTE);
        if (is_string($requestId) && '' !== $requestId) {
            return $requestId;
        }

        $headerRequestId = trim((string) $request->headers->get(DiscoveryOperationLogger::REQUEST_ID_HEADER, ''));

        return '' === $headerRequestId ? null : $headerRequestId;
    }

    private function applyHeaders(JsonResponse $response): JsonResponse
    {
        $response->headers->set(self::API_VERSION_HEADER, self::API_VERSION);
        $response->headers->set(self::SCHEMA_FAMILY_HEADER, DiscoveryApiContract::ENVELOPE_SCHEMA_FAMILY);
        $response->headers->set(self::SCHEMA_VERSION_HEADER, DiscoveryApiContract::ENVELOPE_SCHEMA_VERSION);

        $requestId = $this->resolveRequestId();
        if (is_string($requestId) && '' !== $requestId) {
            $response->headers->set(DiscoveryOperationLogger::REQUEST_ID_HEADER, $requestId);
        }

        return $response;
    }

    private function canonicalPath(string $path): string
    {
        if ('/api/discovery' === $path) {
            return '/api/'.self::API_VERSION.'/discovery';
        }

        if ('/api/discovery/click' === $path) {
            return '/api/'.self::API_VERSION.'/discovery/click';
        }

        return $path;
    }
}
