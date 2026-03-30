<?php

declare(strict_types=1);

namespace App\Service\Discovery\Query;

use App\Dto\Discovery\DiscoveryQuery;
use Symfony\Component\HttpFoundation\Request;

final class DiscoveryQueryFactory
{
    public function fromRequest(Request $request): DiscoveryQuery
    {
        $term = $this->normalizeString($request->query->get('q', $request->query->get('term')));
        $resourceType = $this->normalizeString($request->query->get('resourceType'));
        $status = $this->normalizeString($request->query->get('status'));
        $visibility = $this->normalizeString($request->query->get('visibility'));
        $limit = $this->normalizePositiveInt($request->query->get('limit'), 25, 100);
        $offset = $this->normalizePositiveInt($request->query->get('offset'), 0, 10000);

        return new DiscoveryQuery(
            term: $term,
            resourceType: $resourceType,
            status: $status,
            visibility: $visibility,
            limit: $limit,
            offset: $offset,
        );
    }

    private function normalizeString(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function normalizePositiveInt(mixed $value, int $default, int $max): int
    {
        if (!is_scalar($value) || !is_numeric((string) $value)) {
            return $default;
        }

        $normalized = (int) $value;

        if ($normalized < 0) {
            return $default;
        }

        if ($normalized > $max) {
            return $max;
        }

        return $normalized;
    }
}
