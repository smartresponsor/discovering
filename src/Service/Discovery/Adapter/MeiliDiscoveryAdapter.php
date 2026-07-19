<?php

declare(strict_types=1);

namespace App\Discovering\Service\Discovery\Adapter;

use App\Discovering\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;
use App\Discovering\ServiceInterface\Discovery\Rebuild\DiscoveryStagingCapableAdapterInterface;

/**
 * Implements the meili discovery adapter used by the discovery runtime.
 */
final class MeiliDiscoveryAdapter implements DiscoveryAdapterInterface, DiscoveryStagingCapableAdapterInterface
{
    public function __construct(
        private readonly ?string $base = null,
        private readonly ?string $key = null,
        private readonly string $indexPrefix = '',
    ) {
    }

    /**
     * Performs the upsert operation for this discovery service.
     */
    public function upsert(string $resource, string $id, array $document): void
    {
        $payload = $document;
        $payload['id'] = $id;
        $this->request('POST', sprintf('/indexes/%s/documents', $this->indexUid($resource)), [$payload]);
    }

    /**
     * Performs the remove operation for this discovery service.
     */
    public function remove(string $resource, string $id): void
    {
        $this->request('DELETE', sprintf('/indexes/%s/documents/%s', $this->indexUid($resource), $id));
    }

    /**
     * Executes the search workflow against the active discovery source or backend.
     */
    public function search(string $resource, string $query, int $limit = 20, int $offset = 0): array
    {
        $response = $this->request('POST', sprintf('/indexes/%s/search', $this->indexUid($resource)), ['q' => $query, 'limit' => $limit, 'offset' => $offset]);

        return $response['hits'] ?? [];
    }

    /**
     * Performs the create index operation for this discovery service.
     */
    public function createIndex(string $resource): void
    {
        $this->request('POST', '/indexes', ['uid' => $this->indexUid($resource)]);
    }

    /**
     * Performs the swap alias operation for this discovery service.
     */
    public function swapAlias(string $from, string $to): void
    {
    }

    /**
     * Returns the backend nameEntity value exposed by this service.
     */
    public function getBackendName(): string
    {
        return 'meilisearch';
    }

    /**
     * Performs the supports staged rebuild operation for this discovery service.
     */
    public function supportsStagedRebuild(): bool
    {
        return false;
    }

    private function indexUid(string $resource): string
    {
        $normalized = preg_replace('/[^a-z0-9_]+/i', '_', strtolower($resource)) ?: 'global';
        $normalized = trim($normalized, '_') ?: 'global';
        $prefix = trim($this->indexPrefix);

        if ('' === $prefix) {
            return $normalized;
        }

        $normalizedPrefix = preg_replace('/[^a-z0-9_]+/i', '_', strtolower($prefix)) ?: 'discovery';
        $normalizedPrefix = trim($normalizedPrefix, '_') ?: 'discovery';

        return $normalizedPrefix.'__'.$normalized;
    }

    /** @return array<string, mixed> */
    private function request(string $method, string $path, ?array $body = null): array
    {
        $base = rtrim($this->base ?? (getenv('DISCOVERY_URL') ?: ''), '/');
        if ('' === $base) {
            return [];
        }

        $headers = ['Content-Type: application/json'];
        $key = $this->key ?? (getenv('DISCOVERY_API_KEY') ?: null);
        if (null !== $key && '' !== $key) {
            $headers[] = 'Authorization: Bearer '.$key;
        }

        $context = ['http' => ['method' => $method, 'header' => $headers, 'ignore_errors' => true]];
        if (null !== $body) {
            $context['http']['content'] = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        $raw = @file_get_contents($base.$path, false, stream_context_create($context));
        if (false === $raw || '' === $raw) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
