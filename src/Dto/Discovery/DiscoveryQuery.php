<?php
declare(strict_types=1);

namespace App\Dto\Discovery;


/**
 * Represents the discovery query contract used by discovery application, management, or state coordination flows.
 */
final class DiscoveryQuery
{
    /**
     * @param array<string, scalar|null> $filters
     * @param array<string, float|int> $resourceWeights
     */
    public function __construct(
        public string $query = '',
        public string $resource = 'global',
        public int $limit = 20,
        public int $offset = 0,
        public array $filters = [],
        public array $resourceWeights = [],
        public string $mode = DiscoveryMode::RELEVANCE,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        return new self(
            query: (string) ($payload['query'] ?? ''),
            resource: (string) ($payload['resource'] ?? 'global'),
            limit: max(1, (int) ($payload['limit'] ?? 20)),
            offset: max(0, (int) ($payload['offset'] ?? 0)),
            filters: is_array($payload['filters'] ?? null) ? $payload['filters'] : [],
            resourceWeights: self::normalizeResourceWeights($payload['resourceWeights'] ?? []),
            mode: DiscoveryMode::normalize((string) ($payload['mode'] ?? DiscoveryMode::RELEVANCE)),
        );
    }

    /**
     * @param mixed $payload
     * @return array<string, float>
     */
    private static function normalizeResourceWeights(mixed $payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        $weights = [];
        foreach ($payload as $resource => $weight) {
            if (!is_string($resource) || (!is_float($weight) && !is_int($weight))) {
                continue;
            }

            $weights[$resource] = (float) $weight;
        }

        return $weights;
    }
}
