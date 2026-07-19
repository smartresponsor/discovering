<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Dto\Discovery\DiscoverySourceRecord;
use App\Discovering\Service\Discovery\Source\Support\DiscoverySourceRecordJsonFileEncoder;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the discovery source record json file encoder test case for the Discovering component.
 */
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
