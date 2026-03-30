<?php

declare(strict_types=1);

namespace App\Service\Discovery\Support;

use App\Dto\Discovery\DiscoveryHit;
use App\Dto\Discovery\DiscoveryQuery;
use App\Dto\Discovery\DiscoveryResult;
use App\ValueObject\Discovery\DiscoveryDocument;

final class DiscoveryDocumentMatcher
{
    /**
     * @param list<DiscoveryDocument> $documents
     */
    public function match(array $documents, DiscoveryQuery $query): DiscoveryResult
    {
        $matched = [];
        $normalizedTerm = trim(strtolower((string) $query->term));

        foreach ($documents as $document) {
            if (!$this->matchesFilters($document, $query->filters)) {
                continue;
            }

            $score = $this->scoreDocument($document, $normalizedTerm);

            if ($normalizedTerm !== '' && $score <= 0.0) {
                continue;
            }

            $matched[] = new DiscoveryHit(
                resourceType: $document->resourceType,
                resourceId: $document->resourceId,
                title: $document->title,
                snippet: $this->buildSnippet($document->body),
                score: $score > 0.0 ? $score : 1.0,
                metadata: $document->metadata,
            );
        }

        usort($matched, static function (DiscoveryHit $left, DiscoveryHit $right): int {
            $scoreComparison = $right->score <=> $left->score;

            if ($scoreComparison !== 0) {
                return $scoreComparison;
            }

            return $left->title <=> $right->title;
        });

        $total = count($matched);
        $hits = array_slice($matched, $query->offset, $query->limit);

        return new DiscoveryResult(total: $total, hits: $hits);
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function matchesFilters(DiscoveryDocument $document, array $filters): bool
    {
        foreach ($filters as $name => $expectedValue) {
            if (!array_key_exists($name, $document->filters)) {
                return false;
            }

            if ((string) $document->filters[$name] !== (string) $expectedValue) {
                return false;
            }
        }

        return true;
    }

    private function scoreDocument(DiscoveryDocument $document, string $normalizedTerm): float
    {
        if ($normalizedTerm === '') {
            return 1.0;
        }

        $normalizedTitle = strtolower($document->title);
        $normalizedBody = strtolower($document->body);
        $normalizedMetadata = strtolower(implode(' ', array_map(static fn (mixed $value): string => (string) $value, $document->metadata)));
        $tokens = array_values(array_filter(preg_split('/\s+/', $normalizedTerm) ?: []));

        $score = 0.0;

        if (str_contains($normalizedTitle, $normalizedTerm)) {
            $score += 10.0;
        }

        if (str_contains($normalizedBody, $normalizedTerm)) {
            $score += 5.0;
        }

        foreach ($tokens as $token) {
            if (str_contains($normalizedTitle, $token)) {
                $score += 4.0;
            }

            if (str_contains($normalizedBody, $token)) {
                $score += 2.0;
            }

            if (str_contains($normalizedMetadata, $token)) {
                $score += 1.0;
            }
        }

        return $score;
    }

    private function buildSnippet(string $body): string
    {
        $normalizedBody = trim(preg_replace('/\s+/', ' ', $body) ?? $body);

        if (strlen($normalizedBody) <= 180) {
            return $normalizedBody;
        }

        return substr($normalizedBody, 0, 177) . '...';
    }
}
