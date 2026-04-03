<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\PlaybookManagementActionResult;
use App\Service\Discovery\Playbook\PlaybookOperatorEventTrailBuilder;
use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use App\Tests\Support\DiscoveryTempFilesystemTestCase;

final class PlaybookOperatorEventTrailBuilderTest extends DiscoveryTempFilesystemTestCase
{
    public function testItBuildsTrailFromCurrentRegistryStateAndLastAction(): void
    {
        $projectDir = $this->createTempDirectory('discovering-playbook-trail-');
        $storageDirectory = $projectDir . '/resources/discovery/playbooks';
        mkdir($storageDirectory, 0777, true);
        file_put_contents($storageDirectory . '/alpha.json', json_encode([
            ['resourceId' => 'playbook-alpha', 'title' => 'Alpha', 'body' => 'Alpha body'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        file_put_contents($storageDirectory . '/beta.json', json_encode([
            ['resourceId' => 'playbook-beta', 'title' => 'Beta', 'body' => 'Beta body'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $builder = new PlaybookOperatorEventTrailBuilder(new PlaybookFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        ));

        $events = $builder->build(new PlaybookManagementActionResult(
            actionName: 'audit-registry',
            summary: 'Audited registry.',
            payload: ['fileCount' => 2, 'recordCount' => 2],
        ));

        self::assertSame('surface:load', $events[0]->eventName);
        self::assertSame('info', $events[0]->level);
        self::assertSame('action:audit-registry', $events[1]->eventName);
        self::assertCount(4, $events);
        self::assertSame('registry:file', $events[2]->eventName);
        self::assertSame('registry:file', $events[3]->eventName);

        @unlink($storageDirectory . '/alpha.json');
        @unlink($storageDirectory . '/beta.json');
        @rmdir($storageDirectory);
        @rmdir($projectDir . '/resources/discovery');
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }

    public function testItBuildsWarningTrailWhenRegistryIsEmpty(): void
    {
        $projectDir = $this->createTempDirectory('discovering-playbook-trail-empty-');
        $builder = new PlaybookOperatorEventTrailBuilder(new PlaybookFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        ));

        $events = $builder->build();

        self::assertSame('surface:load', $events[0]->eventName);
        self::assertSame('warning', $events[0]->level);
        self::assertSame('registry:empty', $events[1]->eventName);
        self::assertSame('warning', $events[1]->level);

        @rmdir($projectDir);
    }
}
