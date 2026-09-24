<?php

declare(strict_types=1);

namespace App\Discovering\DTO;

/**
 * Represents the discovery query contract used by discovery application, management, or state coordination flows.
 */
final readonly class DiscoveryQueryDTO
{
    /**
     * @param array<string, scalar|null> $filters
     * @param array<string, float|int>   $resourceWeights
     */
    public function __construct(
        public string $query = '',
        public string $resource = 'global',
        public int $limit = 20,
        public int $offset = 0,
        public array $filters = [],
        public array $resourceWeights = [],
        public string $mode = DiscoveryModeDTO::RELEVANCE,
    ) {
    }

    /**
     * Creates this discovery value from its canonical input array representation.
     *
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            query: (string) ($payload['query'] ?? ''),
            resource: (string) ($payload['resource'] ?? 'global'),
            limit: max(1, (int) ($payload['limit'] ?? 20)),
            offset: max(0, (int) ($payload['offset'] ?? 0)),
            filters: is_array($payload['filters'] ?? null) ? $payload['filters'] : [],
            resourceWeights: self::normalizeResourceWeights($payload['resourceWeights'] ?? []),
            mode: DiscoveryModeDTO::normalize((string) ($payload['mode'] ?? DiscoveryModeDTO::RELEVANCE)),
        );
    }

    /**
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
