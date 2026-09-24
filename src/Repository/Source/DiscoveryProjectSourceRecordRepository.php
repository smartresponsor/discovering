<?php

declare(strict_types=1);

namespace App\Discovering\Repository\Source;

use App\Discovering\DTO\DiscoverySourceRecordDTO;
use App\Discovering\ServiceInterface\Source\Repository\DiscoverySourceRecordRepositoryInterface;

/**
 * Provides project discovery source record access for discovery source and management workflows.
 */
final class DiscoveryProjectSourceRecordRepository implements DiscoverySourceRecordRepositoryInterface
{
    /**
     * Returns the source nameEntity value exposed by this service.
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
            new DiscoverySourceRecordDTO(
                resourceType: 'project',
                resourceId: 'project-smartresponsor-platform',
                title: 'Smart Responsor platform project',
                body: 'Platform-level project coordinating Symfony components, governance, operation, integration, and business capability growth across the ecosystem.',
                filters: ['status' => 'active', 'visibility' => 'internal'],
                metadata: ['tags' => ['platform', 'symfony', 'ecosystem']],
            ),
            new DiscoverySourceRecordDTO(
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
