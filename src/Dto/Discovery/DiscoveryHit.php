<?php
declare(strict_types=1);

namespace App\Dto\Discovery;

final class DiscoveryHit
{
    /**
     * @param list<string> $matchReasons
     * @param list<string> $matchedTokens
     */
    public function __construct(
        public string $id,
        public string $title,
        public string $resource,
        public string $reference = '',
        public string $status = '',
        public float $score = 0.0,
        public array $matchReasons = [],
        public ?float $ftsScore = null,
        public string $highlightedTitle = '',
        public string $highlightedReference = '',
        public array $matchedTokens = [],
    ) {
    }

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        $title = (string) ($payload['title'] ?? $payload['name'] ?? '');
        $reference = (string) ($payload['reference'] ?? $payload['metaCode'] ?? '');

        return new self(
            id: (string) ($payload['id'] ?? ''),
            title: $title,
            resource: (string) ($payload['resource'] ?? 'global'),
            reference: $reference,
            status: (string) ($payload['status'] ?? ''),
            score: is_numeric($payload['score'] ?? null) ? (float) $payload['score'] : 0.0,
            matchReasons: self::normalizeStrings($payload['matchReasons'] ?? []),
            ftsScore: is_numeric($payload['ftsScore'] ?? null) ? (float) $payload['ftsScore'] : null,
            highlightedTitle: (string) ($payload['highlightedTitle'] ?? $title),
            highlightedReference: (string) ($payload['highlightedReference'] ?? $reference),
            matchedTokens: self::normalizeStrings($payload['matchedTokens'] ?? []),
        );
    }

    /**
     * @param mixed $payload
     * @return list<string>
     */
    private static function normalizeStrings(mixed $payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        return array_values(array_filter($payload, 'is_string'));
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'resource' => $this->resource,
            'reference' => $this->reference,
            'status' => $this->status,
            'score' => $this->score,
            'matchReasons' => $this->matchReasons,
            'ftsScore' => $this->ftsScore,
            'highlightedTitle' => $this->highlightedTitle,
            'highlightedReference' => $this->highlightedReference,
            'matchedTokens' => $this->matchedTokens,
        ];
    }
}
