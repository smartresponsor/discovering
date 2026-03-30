<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source;

use App\Dto\Discovery\DiscoverySourceRecord;
use App\ServiceInterface\Discovery\Source\DiscoverySourceProviderInterface;

final class OfferingDiscoverySourceProvider implements DiscoverySourceProviderInterface
{
    public function getResourceType(): string
    {
        return 'offering';
    }

    public function provide(): array
    {
        return [
            new DiscoverySourceRecord(
                resourceType: 'offering',
                resourceId: 'offering-ai-automation-audit',
                title: 'AI automation audit offering',
                body: 'Service offering focused on automation review, agent workflow fit, process bottlenecks, and Symfony-oriented modernization opportunities.',
                filters: ['status' => 'active', 'visibility' => 'public'],
                metadata: ['tags' => ['ai', 'automation', 'audit']],
            ),
            new DiscoverySourceRecord(
                resourceType: 'offering',
                resourceId: 'offering-symfony-modernization',
                title: 'Symfony component modernization offering',
                body: 'Offering for refactoring legacy codebases into coherent Symfony-oriented components with strong manifests, operational surfaces, and growth paths.',
                filters: ['status' => 'active', 'visibility' => 'public'],
                metadata: ['tags' => ['symfony', 'modernization', 'components']],
            ),
        ];
    }
}
