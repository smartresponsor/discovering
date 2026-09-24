<?php

declare(strict_types=1);

namespace App\Discovering\Normalizer\Rebuild;

use App\Discovering\DTO\DiscoveryRebuildSummaryDTO;

/**
 * Handles discovery rebuild evidence json serializer concerns for discovery state, source, or API payloads.
 */
final class DiscoveryRebuildEvidenceJsonSerializer
{
    /**
     * @param list<DiscoveryRebuildSummaryDTO> $summaries
     */
    public function encode(array $summaries): string
    {
        return json_encode(
            array_map(static fn (DiscoveryRebuildSummaryDTO $summary): array => $summary->toArray(), $summaries),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );
    }

    /**
     * @return list<DiscoveryRebuildSummaryDTO>
     */
    public function decode(string $payload): array
    {
        if ('' === trim($payload)) {
            return [];
        }

        $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            return [];
        }

        $summaries = [];
        foreach ($decoded as $row) {
            if (!is_array($row)) {
                continue;
            }

            $indexedCountsByResource = $row['indexedCountsByResource'] ?? [];
            $stagedIndexes = $row['stagedIndexes'] ?? [];
            $summaries[] = new DiscoveryRebuildSummaryDTO(
                evidenceId: (string) ($row['evidenceId'] ?? ''),
                resource: (string) ($row['resource'] ?? 'global'),
                rebuildMode: (string) ($row['rebuildMode'] ?? 'full'),
                backendName: (string) ($row['backendName'] ?? 'unknown'),
                deploymentMode: (string) ($row['deploymentMode'] ?? 'in_place'),
                zeroDowntimeReady: (bool) ($row['zeroDowntimeReady'] ?? false),
                startedAt: (string) ($row['startedAt'] ?? ''),
                finishedAt: (string) ($row['finishedAt'] ?? ''),
                candidateDocumentCount: (int) ($row['candidateDocumentCount'] ?? 0),
                indexedDocumentCount: (int) ($row['indexedDocumentCount'] ?? 0),
                skippedDocumentCount: (int) ($row['skippedDocumentCount'] ?? 0),
                indexedCountsByResource: is_array($indexedCountsByResource) ? array_map('intval', $indexedCountsByResource) : [],
                stagedIndexes: is_array($stagedIndexes) ? array_map('strval', $stagedIndexes) : [],
                aliasSwapApplied: (bool) ($row['aliasSwapApplied'] ?? false),
            );
        }

        return $summaries;
    }
}
