<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Builder\Briefing\DiscoveryBriefingOperatorEventTrailBuilder;
use App\Discovering\DTO\DiscoveryBriefingManagementActionResultDTO;
use App\Discovering\Repository\Source\DiscoveryBriefingFileSourceRecordRepository;
use App\Discovering\Repository\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Discovering\Repository\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;

/**
 * Exercises the briefing operator event trail builder test case for the Discovering component.
 */
final class BriefingOperatorEventTrailBuilderTest extends DiscoveryTempFilesystemTestCase
{
    public function testItBuildsTrailFromCurrentRegistryStateAndLastAction(): void
    {
        $projectDir = $this->createTempProjectDirectory('discovering-briefing-trail-');
        $this->writeDiscoveryRegistryFile($projectDir, 'briefings', 'alpha.json', [
            ['resourceId' => 'briefing-alpha', 'title' => 'Alpha', 'body' => 'Alpha body'],
        ]);
        $this->writeDiscoveryRegistryFile($projectDir, 'briefings', 'beta.json', [
            ['resourceId' => 'briefing-beta', 'title' => 'Beta', 'body' => 'Beta body'],
        ]);

        $builder = new DiscoveryBriefingOperatorEventTrailBuilder(new DiscoveryBriefingFileSourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        ));

        $events = $builder->build(new DiscoveryBriefingManagementActionResultDTO(
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
    }

    public function testItBuildsWarningTrailWhenRegistryIsEmpty(): void
    {
        $projectDir = $this->createTempProjectDirectory('discovering-briefing-trail-empty-');
        $builder = new DiscoveryBriefingOperatorEventTrailBuilder(new DiscoveryBriefingFileSourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        ));

        $events = $builder->build();

        self::assertSame('surface:load', $events[0]->eventName);
        self::assertSame('warning', $events[0]->level);
        self::assertSame('registry:empty', $events[1]->eventName);
        self::assertSame('warning', $events[1]->level);
    }
}
