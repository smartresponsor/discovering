<?php

declare(strict_types=1);

namespace App\Discovering\Tests\Unit\Discovery;

use App\Discovering\Dto\Discovery\DiscoverySourceRecord;
use App\Discovering\Service\Discovery\Document\DiscoveryDocumentFactory;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the discovery document factory test case for the Discovering component.
 */
final class DiscoveryDocumentFactoryTest extends TestCase
{
    public function testItBuildsDocumentFromSourceRecordWithReferenceFallback(): void
    {
        $factory = new DiscoveryDocumentFactory();
        $document = $factory->createFromSourceRecord(new DiscoverySourceRecord(
            resourceType: 'briefing',
            resourceId: 'briefing-live-source-governance',
            title: 'Live source governance briefing',
            body: 'Governance checklist and operational guidance.',
            filters: ['status' => 'active'],
            metadata: [],
        ));

        self::assertSame('briefing', $document->resource);
        self::assertSame('briefing-live-source-governance', $document->id);
        self::assertSame('briefing-live-source-governance', $document->reference);
        self::assertSame('active', $document->status);
        self::assertSame('Governance checklist and operational guidance.', $document->content);
        self::assertJson($document->fields['filters']);
        self::assertJson($document->fields['metadata']);
    }

    public function testItPrefersStringReferenceFromMetadata(): void
    {
        $factory = new DiscoveryDocumentFactory();
        $document = $factory->createFromSourceRecord(new DiscoverySourceRecord(
            resourceType: 'playbook',
            resourceId: 'playbook-rollout',
            title: 'Rollout playbook',
            body: 'Canary release instructions.',
            filters: [],
            metadata: ['reference' => 'docs/playbooks/rollout.md'],
        ));

        self::assertSame('docs/playbooks/rollout.md', $document->reference);
        self::assertSame('', $document->status);
    }
}
