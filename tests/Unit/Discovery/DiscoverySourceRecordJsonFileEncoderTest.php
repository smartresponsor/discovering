<?php

declare(strict_types=1);

namespace App\Tests\Unit\Discovery;

use App\Dto\Discovery\DiscoverySourceRecord;
use App\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use PHPUnit\Framework\TestCase;

final class DiscoverySourceRecordJsonFileEncoderTest extends TestCase
{
    public function testItEncodesRecordsIntoPrettyJson(): void
    {
        $encoder = new DiscoverySourceRecordJsonFileEncoder();
        $json = $encoder->encodeRecords([
            new DiscoverySourceRecord(
                resourceType: 'playbook',
                resourceId: 'playbook-alpha',
                title: 'Alpha playbook',
                body: 'Body',
                filters: ['status' => 'active'],
                metadata: ['tags' => ['alpha']],
            ),
        ]);

        self::assertStringContainsString('"resourceId": "playbook-alpha"', $json);
        self::assertStringContainsString('"resourceType": "playbook"', $json);
        self::assertStringContainsString('"status": "active"', $json);
    }
}
