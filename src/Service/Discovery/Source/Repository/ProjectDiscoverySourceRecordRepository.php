<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source\Repository;

use App\Dto\Discovery\DiscoverySourceRecord;

final class ProjectDiscoverySourceRecordRepository implements DiscoverySourceRecordRepositoryInterface
{
    public function getSourceName(): string
    {
        return 'project-source-provider';
    }

    public function getResourceType(): string
    {
        return 'project';
    }

    public function all(): array
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
