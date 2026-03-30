<?php
declare(strict_types=1);

namespace App\Service\Discovery\Adapter;

use App\ServiceInterface\Discovery\Adapter\DiscoveryAdapterInterface;

final class MeiliDiscoveryAdapter implements DiscoveryAdapterInterface
{
    public function __construct(private readonly ?string $base = null, private readonly ?string $key = null)
    {
    }

    public function upsert(string $resource, string $id, array $document): void
    {
        $payload = $document;
        $payload['id'] = $id;
        $this->request('POST', sprintf('/indexes/%s/documents', $resource), [$payload]);
    }

    public function remove(string $resource, string $id): void
    {
        $this->request('DELETE', sprintf('/indexes/%s/documents/%s', $resource, $id));
    }

    public function search(string $resource, string $query, int $limit = 20, int $offset = 0): array
    {
        $response = $this->request('POST', sprintf('/indexes/%s/search', $resource), ['q' => $query, 'limit' => $limit, 'offset' => $offset]);
        return $response['hits'] ?? [];
    }

    public function createIndex(string $resource): void
    {
        $this->request('POST', '/indexes', ['uid' => $resource]);
    }

    public function swapAlias(string $from, string $to): void
    {
    }

    /** @return array<string, mixed> */
    private function request(string $method, string $path, ?array $body = null): array
    {
        $base = rtrim($this->base ?? (getenv('DISCOVERY_URL') ?: ''), '/');
        if ($base === '') {
            return [];
        }

        $headers = ["Content-Type: application/json"];
        $key = $this->key ?? (getenv('DISCOVERY_API_KEY') ?: null);
        if ($key !== null && $key !== '') {
            $headers[] = 'Authorization: Bearer ' . $key;
        }

        $context = ['http' => ['method' => $method, 'header' => $headers, 'ignore_errors' => true]];
        if ($body !== null) {
            $context['http']['content'] = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        $raw = @file_get_contents($base . $path, false, stream_context_create($context));
        if ($raw === false || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}
