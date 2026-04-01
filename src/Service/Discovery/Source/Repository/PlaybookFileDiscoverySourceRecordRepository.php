<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source\Repository;

use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;

final class PlaybookFileDiscoverySourceRecordRepository implements DiscoverySourceRecordRepositoryInterface
{
    public function __construct(
        private readonly string $projectDir,
        private readonly DiscoverySourceRecordJsonFileDecoder $decoder,
    ) {
    }

    public function getSourceName(): string
    {
        return 'playbook-file-source-provider';
    }

    public function getResourceType(): string
    {
        return 'playbook';
    }

    public function all(): array
    {
        return $this->decoder->decodeFile(
            $this->projectDir . '/resources/discovery/playbook_source_records.json',
            $this->getResourceType(),
        );
    }
}
