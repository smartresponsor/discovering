<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source\Repository;

use App\Dto\Discovery\DiscoverySourceRecord;


/**
 * Provides category discovery source record access for discovery source and management workflows.
 */
final class CategoryDiscoverySourceRecordRepository implements DiscoverySourceRecordRepositoryInterface
{
    /**
     * Returns the source name value exposed by this service.
     */
    public function getSourceName(): string
    {
        return 'category-source-provider';
    }

    /**
     * Returns the resource type value exposed by this service.
     */
    public function getResourceType(): string
    {
        return 'category';
    }

    /**
     * Performs the all operation for this discovery service.
     */
    public function all(): array
    {
        return [
            new DiscoverySourceRecord(
                resourceType: 'category',
                resourceId: 'category-automation',
                title: 'Automation category',
                body: 'Category grouping automation-oriented resources, delivery offerings, diagnostic guides, and operational templates.',
                filters: ['status' => 'active', 'visibility' => 'public'],
                metadata: ['tags' => ['automation', 'category']],
            ),
            new DiscoverySourceRecord(
                resourceType: 'category',
                resourceId: 'category-governance',
                title: 'Governance category',
                body: 'Category for governance-related resources such as canon rules, architecture manifests, diagnostics, and policy-oriented guidance.',
                filters: ['status' => 'active', 'visibility' => 'internal'],
                metadata: ['tags' => ['governance', 'policy', 'canon']],
            ),
        ];
    }
}
