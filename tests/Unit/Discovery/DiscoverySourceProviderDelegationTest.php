<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Service\Discovery\Source\BriefingDiscoverySourceProvider;
use App\Service\Discovery\Source\CategoryDiscoverySourceProvider;
use App\Service\Discovery\Source\DocumentDiscoverySourceProvider;
use App\Service\Discovery\Source\OfferingDiscoverySourceProvider;
use App\Service\Discovery\Source\PlaybookDiscoverySourceProvider;
use App\Service\Discovery\Source\ProjectDiscoverySourceProvider;
use App\Service\Discovery\Source\Repository\BriefingFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\CategoryDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\DocumentDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\OfferingDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Repository\ProjectDiscoverySourceRecordRepository;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use App\Tests\Support\DiscoveryTempFilesystemTestCase;


/**
 * Exercises the discovery source provider delegation test case for the Discovering component.
 */
final class DiscoverySourceProviderDelegationTest extends DiscoveryTempFilesystemTestCase
{
    public function testProjectProviderDelegatesToRepository(): void
    {
        $provider = new ProjectDiscoverySourceProvider(new ProjectDiscoverySourceRecordRepository());

        self::assertSame('project-source-provider', $provider->getSourceName());
        self::assertSame('project', $provider->getResourceType());
        self::assertCount(2, $provider->provide());
    }

    public function testOfferingProviderDelegatesToRepository(): void
    {
        $provider = new OfferingDiscoverySourceProvider(new OfferingDiscoverySourceRecordRepository());

        self::assertSame('offering-source-provider', $provider->getSourceName());
        self::assertSame('offering', $provider->getResourceType());
        self::assertCount(2, $provider->provide());
    }

    public function testDocumentProviderDelegatesToRepository(): void
    {
        $provider = new DocumentDiscoverySourceProvider(new DocumentDiscoverySourceRecordRepository());

        self::assertSame('document-source-provider', $provider->getSourceName());
        self::assertSame('document', $provider->getResourceType());
        self::assertCount(2, $provider->provide());
    }

    public function testCategoryProviderDelegatesToRepository(): void
    {
        $provider = new CategoryDiscoverySourceProvider(new CategoryDiscoverySourceRecordRepository());

        self::assertSame('category-source-provider', $provider->getSourceName());
        self::assertSame('category', $provider->getResourceType());
        self::assertCount(2, $provider->provide());
    }

    public function testPlaybookProviderDelegatesToFileBackedRepository(): void
    {
        $projectDir = $this->createTempDirectory('discovering-provider-playbook-');
        $resourceDirectory = $projectDir . '/resources/discovery';

        mkdir($resourceDirectory, 0777, true);
        file_put_contents($resourceDirectory . '/playbook_source_records.json', json_encode([
            [
                'resourceId' => 'playbook-sample',
                'title' => 'Sample playbook',
                'body' => 'Sample body',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $provider = new PlaybookDiscoverySourceProvider(new PlaybookFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        ));

        self::assertSame('playbook-file-source-provider', $provider->getSourceName());
        self::assertSame('playbook', $provider->getResourceType());
        self::assertCount(1, $provider->provide());

        @unlink($resourceDirectory . '/playbook_source_records.json');
        @rmdir($resourceDirectory);
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }

    public function testBriefingProviderDelegatesToFileBackedRepository(): void
    {
        $projectDir = $this->createTempDirectory('discovering-provider-briefing-');
        $storageDirectory = $projectDir . '/resources/discovery/briefings';

        mkdir($storageDirectory, 0777, true);
        file_put_contents($storageDirectory . '/sample.json', json_encode([
            [
                'resourceId' => 'briefing-sample',
                'title' => 'Sample briefing',
                'body' => 'Sample body',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $provider = new BriefingDiscoverySourceProvider(new BriefingFileDiscoverySourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        ));

        self::assertSame('briefing-file-source-provider', $provider->getSourceName());
        self::assertSame('briefing', $provider->getResourceType());
        self::assertCount(1, $provider->provide());

        @unlink($storageDirectory . '/sample.json');
        @rmdir($storageDirectory);
        @rmdir($projectDir . '/resources/discovery');
        @rmdir($projectDir . '/resources');
        @rmdir($projectDir);
    }
}
