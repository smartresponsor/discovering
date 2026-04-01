<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source\Support;

use App\Dto\Discovery\DiscoverySourceRecord;

final class DiscoverySourceRecordJsonFileDecoder
{
    /**
     * @return list<DiscoverySourceRecord>
     */
    public function decodeFile(string $path, string $defaultResourceType): array
    {
        if (!is_file($path)) {
            return [];
        }

        $contents = file_get_contents($path);

        if ($contents === false || trim($contents) === '') {
            return [];
        }

        $payload = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        if (!is_array($payload)) {
            return [];
        }

        $records = [];

        foreach ($payload as $item) {
            if (!is_array($item)) {
                continue;
            }

            $resourceId = $item['resourceId'] ?? null;
            $title = $item['title'] ?? null;
            $body = $item['body'] ?? null;

            if (!is_string($resourceId) || !is_string($title) || !is_string($body)) {
                continue;
            }

            $resourceType = $item['resourceType'] ?? $defaultResourceType;
            $filters = $item['filters'] ?? [];
            $metadata = $item['metadata'] ?? [];

            $records[] = new DiscoverySourceRecord(
                resourceType: is_string($resourceType) && $resourceType !== '' ? $resourceType : $defaultResourceType,
                resourceId: $resourceId,
                title: $title,
                body: $body,
                filters: is_array($filters) ? $filters : [],
                metadata: is_array($metadata) ? $metadata : [],
            );
        }

        return $records;
    }
}
