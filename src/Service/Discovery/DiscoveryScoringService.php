<?php
declare(strict_types=1);

namespace App\Service\Discovery;

use App\Dto\Discovery\DiscoveryHit;
use App\Dto\Discovery\DiscoveryQuery;

final class DiscoveryScoringService
{
    /**
     * @param list<DiscoveryHit> $hits
     * @return list<DiscoveryHit>
     */
    public function rank(array $hits, DiscoveryQuery $query): array
    {
        $filteredHits = array_values(array_filter(
            $hits,
            fn (DiscoveryHit $hit): bool => $this->matchesFilters($hit, $query),
        ));

        if (trim($query->query) === '') {
            return $filteredHits;
        }

        $scoredHits = array_map(
            fn (DiscoveryHit $hit): DiscoveryHit => $this->scoreHit($hit, $query),
            $filteredHits,
        );

        usort($scoredHits, static function (DiscoveryHit $left, DiscoveryHit $right): int {
            if ($left->score === $right->score) {
                return [$left->title, $left->id] <=> [$right->title, $right->id];
            }

            return $right->score <=> $left->score;
        });

        return $scoredHits;
    }

    private function matchesFilters(DiscoveryHit $hit, DiscoveryQuery $query): bool
    {
        $statusFilter = $query->filters['status'] ?? null;
        if (is_string($statusFilter) && $statusFilter !== '' && $hit->status !== $statusFilter) {
            return false;
        }

        $resourceFilter = $query->filters['resource'] ?? null;
        if (is_string($resourceFilter) && $resourceFilter !== '' && $hit->resource !== $resourceFilter) {
            return false;
        }

        return true;
    }

    private function scoreHit(DiscoveryHit $hit, DiscoveryQuery $query): DiscoveryHit
    {
        $phrase = $this->normalize($query->query);
        $tokens = $this->tokenize($query->query);
        $title = $this->normalize($hit->title);
        $reference = $this->normalize($hit->reference);
        $resource = $this->normalize($hit->resource);
        $status = $this->normalize($hit->status);
        $combined = trim(implode(' ', [$title, $reference, $resource, $status]));

        $score = 0.0;
        $reasons = [];

        if ($phrase !== '' && str_contains($title, $phrase)) {
            $score += 20.0;
            $reasons[] = 'title phrase match';
        }

        if ($phrase !== '' && $reference !== '' && str_contains($reference, $phrase)) {
            $score += 12.0;
            $reasons[] = 'reference phrase match';
        }

        foreach ($tokens as $token) {
            if (str_contains($title, $token)) {
                $score += 6.0;
                $reasons[] = sprintf('title token: %s', $token);
            }

            if ($reference !== '' && str_contains($reference, $token)) {
                $score += 4.0;
                $reasons[] = sprintf('reference token: %s', $token);
            }

            if ($resource === $token) {
                $score += 3.0;
                $reasons[] = sprintf('resource token: %s', $token);
            }

            if ($status !== '' && $status === $token) {
                $score += 2.0;
                $reasons[] = sprintf('status token: %s', $token);
            }
        }

        if ($tokens !== [] && $this->allTokensPresent($tokens, $combined)) {
            $score += 8.0;
            $reasons[] = 'all query tokens matched';
        }

        $resourceWeight = $query->resourceWeights[$hit->resource] ?? 1.0;
        if ($resourceWeight !== 1.0 && $score > 0.0) {
            $score *= $resourceWeight;
            $reasons[] = sprintf('resource weight %.2f', $resourceWeight);
        }

        return new DiscoveryHit(
            id: $hit->id,
            title: $hit->title,
            resource: $hit->resource,
            reference: $hit->reference,
            status: $hit->status,
            score: round($score, 2),
            matchReasons: array_values(array_unique($reasons)),
        );
    }

    /** @return list<string> */
    private function tokenize(string $query): array
    {
        $parts = preg_split('/[^a-z0-9]+/i', strtolower($query)) ?: [];
        $tokens = array_values(array_filter($parts, static fn (string $part): bool => $part !== ''));

        return array_values(array_unique($tokens));
    }

    private function normalize(string $value): string
    {
        return trim(strtolower($value));
    }

    /**
     * @param list<string> $tokens
     */
    private function allTokensPresent(array $tokens, string $haystack): bool
    {
        foreach ($tokens as $token) {
            if (!str_contains($haystack, $token)) {
                return false;
            }
        }

        return true;
    }
}
