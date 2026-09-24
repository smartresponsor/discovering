<?php

declare(strict_types=1);

namespace App\Discovering\Service;

use App\Discovering\DTO\DiscoveryHitDTO;
use App\Discovering\DTO\DiscoveryQueryDTO;

/**
 * Provides the discovery scoring capability within the discovery component.
 */
final class DiscoveryScoringService
{
    public function __construct(
        private readonly DiscoveryHighlightingService $highlightingService,
        private readonly DiscoveryLearningService $learningService,
    ) {
    }

    /**
     * @param list<DiscoveryHitDTO> $hits
     *
     * @return list<DiscoveryHitDTO>
     */
    public function rank(array $hits, DiscoveryQueryDTO $query): array
    {
        $filteredHits = array_values(array_filter(
            $hits,
            fn (DiscoveryHitDTO $hit): bool => $this->matchesFilters($hit, $query),
        ));

        if ('' === trim($query->query)) {
            return $filteredHits;
        }

        $scoredHits = array_map(
            fn (DiscoveryHitDTO $hit): DiscoveryHitDTO => $this->scoreHit($hit, $query),
            $filteredHits,
        );

        usort($scoredHits, static function (DiscoveryHitDTO $left, DiscoveryHitDTO $right): int {
            if ($left->score === $right->score) {
                return [$left->title, $left->id] <=> [$right->title, $right->id];
            }

            return $right->score <=> $left->score;
        });

        return $scoredHits;
    }

    private function matchesFilters(DiscoveryHitDTO $hit, DiscoveryQueryDTO $query): bool
    {
        $statusFilter = $query->filters['status'] ?? null;
        if (is_string($statusFilter) && '' !== $statusFilter && $hit->status !== $statusFilter) {
            return false;
        }

        $resourceFilter = $query->filters['resource'] ?? null;
        if (is_string($resourceFilter) && '' !== $resourceFilter && $hit->resource !== $resourceFilter) {
            return false;
        }

        return true;
    }

    private function scoreHit(DiscoveryHitDTO $hit, DiscoveryQueryDTO $query): DiscoveryHitDTO
    {
        $phrase = $this->normalize($query->query);
        $tokens = $this->tokenize($query->query);
        $title = $this->normalize($hit->title);
        $reference = $this->normalize($hit->reference);
        $resource = $this->normalize($hit->resource);
        $status = $this->normalize($hit->status);
        $content = $this->normalize($hit->content);
        $combined = trim(implode(' ', [$title, $reference, $resource, $status, $content]));

        $customScore = 0.0;
        $reasons = [];
        $matchedTokens = [];

        if ('' !== $phrase && str_contains($title, $phrase)) {
            $customScore += 20.0;
            $reasons[] = 'title phrase match';
        }

        if ('' !== $phrase && '' !== $reference && str_contains($reference, $phrase)) {
            $customScore += 12.0;
            $reasons[] = 'reference phrase match';
        }

        if ('' !== $phrase && '' !== $content && str_contains($content, $phrase)) {
            $customScore += 10.0;
            $reasons[] = 'content phrase match';
        }

        foreach ($tokens as $token) {
            if (str_contains($title, $token)) {
                $customScore += 6.0;
                $reasons[] = sprintf('title token: %s', $token);
                $matchedTokens[] = $token;
            }

            if ('' !== $reference && str_contains($reference, $token)) {
                $customScore += 4.0;
                $reasons[] = sprintf('reference token: %s', $token);
                $matchedTokens[] = $token;
            }

            if ('' !== $content && str_contains($content, $token)) {
                $customScore += 3.5;
                $reasons[] = sprintf('content token: %s', $token);
                $matchedTokens[] = $token;
            }

            if ($resource === $token) {
                $customScore += 3.0;
                $reasons[] = sprintf('resource token: %s', $token);
                $matchedTokens[] = $token;
            }

            if ('' !== $status && $status === $token) {
                $customScore += 2.0;
                $reasons[] = sprintf('status token: %s', $token);
                $matchedTokens[] = $token;
            }
        }

        if ([] !== $tokens && $this->allTokensPresent($tokens, $combined)) {
            $customScore += 8.0;
            $reasons[] = 'all query tokens matched';
        }

        $resourceWeight = $query->resourceWeights[$hit->resource] ?? 1.0;
        if (1.0 !== $resourceWeight && $customScore > 0.0) {
            $customScore *= $resourceWeight;
            $reasons[] = sprintf('resource weight %.2f', $resourceWeight);
        }

        $ftsScore = $hit->ftsScore;
        $ftsBoost = $this->calculateFtsBoost($ftsScore);
        if ($ftsBoost > 0.0) {
            $reasons[] = sprintf('fts boost %.2f', $ftsBoost);
        }

        $feedbackCount = $this->learningService->getFeedbackCount($hit);
        $feedbackBoost = $this->learningService->calculateFeedbackBoost($feedbackCount);
        if ($feedbackBoost > 0.0) {
            $reasons[] = sprintf('feedback boost %.2f', $feedbackBoost);
        }

        $matchedTokens = array_values(array_unique($matchedTokens));
        $snippet = $this->highlightingService->buildSnippet($hit->content, $matchedTokens);
        $finalScore = round($customScore + $ftsBoost + $feedbackBoost, 2);

        return new DiscoveryHitDTO(
            id: $hit->id,
            title: $hit->title,
            resource: $hit->resource,
            reference: $hit->reference,
            status: $hit->status,
            content: $hit->content,
            score: $finalScore,
            matchReasons: array_values(array_unique($reasons)),
            ftsScore: $ftsScore,
            highlightedTitle: $this->highlightingService->highlight($hit->title, $matchedTokens),
            highlightedReference: $this->highlightingService->highlight($hit->reference, $matchedTokens),
            highlightedSnippet: $this->highlightingService->highlight($snippet, $matchedTokens),
            matchedTokens: $matchedTokens,
            feedbackCount: $feedbackCount,
            feedbackBoost: $feedbackBoost,
        );
    }

    /** @return list<string> */
    private function tokenize(string $query): array
    {
        $parts = preg_split('/[^a-z0-9]+/i', strtolower($query)) ?: [];
        $tokens = array_values(array_filter($parts, static fn (string $part): bool => '' !== $part));

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

    private function calculateFtsBoost(?float $ftsScore): float
    {
        if (null === $ftsScore) {
            return 0.0;
        }

        $normalized = 1 / (1 + abs($ftsScore));

        return round($normalized * 10, 2);
    }
}
