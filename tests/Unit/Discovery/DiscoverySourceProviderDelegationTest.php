<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Provider\Source\DiscoveryBriefingSourceProvider;
use App\Discovering\Provider\Source\DiscoveryCategorySourceProvider;
use App\Discovering\Provider\Source\DiscoveryDocumentSourceProvider;
use App\Discovering\Provider\Source\DiscoveryOfferingSourceProvider;
use App\Discovering\Provider\Source\DiscoveryPlaybookSourceProvider;
use App\Discovering\Provider\Source\DiscoveryProjectSourceProvider;
use App\Discovering\Repository\Source\DiscoveryBriefingFileSourceRecordRepository;
use App\Discovering\Repository\Source\DiscoveryCategorySourceRecordRepository;
use App\Discovering\Repository\Source\DiscoveryDocumentSourceRecordRepository;
use App\Discovering\Repository\Source\DiscoveryOfferingSourceRecordRepository;
use App\Discovering\Repository\Source\DiscoveryPlaybookFileSourceRecordRepository;
use App\Discovering\Repository\Source\DiscoveryProjectSourceRecordRepository;
use App\Discovering\Repository\Source\Support\DiscoverySourceRecordJsonFileDecoder;
use App\Discovering\Repository\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use App\Discovering\Tests\Support\DiscoveryTempFilesystemTestCase;

/**
 * Exercises the discovery source provider delegation test case for the Discovering component.
 */
final class DiscoverySourceProviderDelegationTest extends DiscoveryTempFilesystemTestCase
{
    public function testProjectProviderDelegatesToRepository(): void
    {
        $provider = new DiscoveryProjectSourceProvider(new DiscoveryProjectSourceRecordRepository());

        self::assertSame('project-source-provider', $provider->getSourceName());
        self::assertSame('project', $provider->getResourceType());
        self::assertCount(2, $provider->provide());
    }

    public function testOfferingProviderDelegatesToRepository(): void
    {
        $provider = new DiscoveryOfferingSourceProvider(new DiscoveryOfferingSourceRecordRepository());

        self::assertSame('offering-source-provider', $provider->getSourceName());
        self::assertSame('offering', $provider->getResourceType());
        self::assertCount(2, $provider->provide());
    }

    public function testDocumentProviderDelegatesToRepository(): void
    {
        $provider = new DiscoveryDocumentSourceProvider(new DiscoveryDocumentSourceRecordRepository());

        self::assertSame('document-source-provider', $provider->getSourceName());
        self::assertSame('document', $provider->getResourceType());
        self::assertCount(2, $provider->provide());
    }

    public function testCategoryProviderDelegatesToRepository(): void
    {
        $provider = new DiscoveryCategorySourceProvider(new DiscoveryCategorySourceRecordRepository());

        self::assertSame('category-source-provider', $provider->getSourceName());
        self::assertSame('category', $provider->getResourceType());
        self::assertCount(2, $provider->provide());
    }

    public function testPlaybookProviderDelegatesToFileBackedRepository(): void
    {
        $projectDir = $this->createTempDirectory('discovering-provider-playbook-');
        $resourceDirectory = $projectDir.'/resources/discovery';

        mkdir($resourceDirectory, 0777, true);
        file_put_contents($resourceDirectory.'/playbook_source_records.json', json_encode([
            [
                'resourceId' => 'playbook-sample',
                'title' => 'Sample playbook',
                'body' => 'Sample body',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $provider = new DiscoveryPlaybookSourceProvider(new DiscoveryPlaybookFileSourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        ));

        self::assertSame('playbook-file-source-provider', $provider->getSourceName());
        self::assertSame('playbook', $provider->getResourceType());
        self::assertCount(1, $provider->provide());

        @unlink($resourceDirectory.'/playbook_source_records.json');
        @rmdir($resourceDirectory);
        @rmdir($projectDir.'/resources');
        @rmdir($projectDir);
    }

    public function testBriefingProviderDelegatesToFileBackedRepository(): void
    {
        $projectDir = $this->createTempDirectory('discovering-provider-briefing-');
        $storageDirectory = $projectDir.'/resources/discovery/briefings';

        mkdir($storageDirectory, 0777, true);
        file_put_contents($storageDirectory.'/sample.json', json_encode([
            [
                'resourceId' => 'briefing-sample',
                'title' => 'Sample briefing',
                'body' => 'Sample body',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $provider = new DiscoveryBriefingSourceProvider(new DiscoveryBriefingFileSourceRecordRepository(
            $projectDir,
            new DiscoverySourceRecordJsonFileDecoder(),
            new DiscoverySourceRecordJsonFileEncoder(),
        ));

        self::assertSame('briefing-file-source-provider', $provider->getSourceName());
        self::assertSame('briefing', $provider->getResourceType());
        self::assertCount(1, $provider->provide());

        @unlink($storageDirectory.'/sample.json');
        @rmdir($storageDirectory);
        @rmdir($projectDir.'/resources/discovery');
        @rmdir($projectDir.'/resources');
        @rmdir($projectDir);
    }
}
