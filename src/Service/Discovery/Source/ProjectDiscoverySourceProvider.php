<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source;

use App\Dto\Discovery\DiscoverySourceRecord;
use App\ServiceInterface\Discovery\Source\DiscoverySourceProviderInterface;

final class ProjectDiscoverySourceProvider implements DiscoverySourceProviderInterface
{
    public function getResourceType(): string
    {
        return 'project';
    }

    public function provide(): array
    {
        return [
            new DiscoverySourceRecord(
                resourceType: 'project',
                resourceId: 'project-smartresponsor-platform',
                title: 'Smart Responsor platform project',
                body: 'Platform-level project coordinating Symfony components, governance, operation, integration, and business capability growth across the ecosystem.',
                filters: ['status' => 'active', 'visibility' => 'internal'],
                metadata: ['tags' => ['platform', 'symfony', 'ecosystem']],
            ),
            new DiscoverySourceRecord(
                resourceType: 'project',
                resourceId: 'project-discovery-workspace',
                title: 'Discovering workspace rollout',
                body: 'Workspace-level initiative for building Scout-like application resource discovery with indexing, retrieval, filtering, diagnostics, and management screens.',
                filters: ['status' => 'active', 'visibility' => 'internal'],
                metadata: ['tags' => ['discovery', 'indexing', 'retrieval']],
            ),
        ];
    }
}
