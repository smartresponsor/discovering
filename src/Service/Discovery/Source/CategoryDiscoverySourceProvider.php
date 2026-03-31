<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source;

use App\Dto\Discovery\DiscoverySourceRecord;
use App\ServiceInterface\Discovery\Source\DiscoverySourceProviderInterface;

final class CategoryDiscoverySourceProvider implements DiscoverySourceProviderInterface
{
    public function getSourceName(): string
    {
        return 'category-source-provider';
    }

    public function getResourceType(): string
    {
        return 'category';
    }

    public function provide(): array
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
