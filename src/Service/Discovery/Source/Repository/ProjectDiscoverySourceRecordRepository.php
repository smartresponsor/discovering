<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source\Repository;

use App\Dto\Discovery\DiscoverySourceRecord;


/**
 * Provides project discovery source record access for discovery source and management workflows.
 */
final class ProjectDiscoverySourceRecordRepository implements DiscoverySourceRecordRepositoryInterface
{
    /**
     * Returns the source name value exposed by this service.
     */
    public function getSourceName(): string
    {
        return 'project-source-provider';
    }

    /**
     * Returns the resource type value exposed by this service.
     */
    public function getResourceType(): string
    {
        return 'project';
    }

    /**
     * Performs the all operation for this discovery service.
     */
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
