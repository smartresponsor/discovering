<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use PHPUnit\Framework\TestCase;

final class PlaybookFileDiscoverySourceRecordRepositoryTest extends TestCase
{
    public function testItLoadsPlaybookRecordsFromJsonFile(): void
    {
        $projectDir = sys_get_temp_dir() . '/discovering-playbook-' . uniqid('', true);
        $resourceDirectory = $projectDir . '/resources/discovery';

        mkdir($resourceDirectory, 0777, true);
        file_put_contents($resourceDirectory . '/playbook_source_records.json', json_encode([
            [
                'resourceId' => 'playbook-alpha',
                'title' => 'Alpha playbook',
                'body' => 'Alpha body',
                'filters' => ['status' => 'active', 'visibility' => 'internal'],
                'metadata' => ['tags' => ['alpha']],
            ],
            [
                'resourceId' => 'playbook-beta',
                'title' => 'Beta playbook',
                'body' => 'Beta body',
                'resourceType' => 'special-playbook',
                'filters' => ['status' => 'draft', 'visibility' => 'internal'],
                'metadata' => ['tags' => ['beta']],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $repository = new PlaybookFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
        );

        $records = $repository->all();

        self::assertSame('playbook-file-source-provider', $repository->getSourceName());
        self::assertSame('playbook', $repository->getResourceType());
        self::assertCount(2, $records);
        self::assertSame('playbook-alpha', $records[0]->resourceId);
        self::assertSame('playbook', $records[0]->resourceType);
        self::assertSame('special-playbook', $records[1]->resourceType);

        @unlink($resourceDirectory . '/playbook_source_records.json');
        @rmdir($resourceDirectory);
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }

    public function testItReturnsEmptyListWhenJsonFileDoesNotExist(): void
    {
        $repository = new PlaybookFileDiscoverySourceRecordRepository(
            sys_get_temp_dir() . '/discovering-playbook-missing-' . uniqid('', true),
            new DiscoverySourceRecordJsonFileDecoder(),
        );

        self::assertSame([], $repository->all());
    }
}
