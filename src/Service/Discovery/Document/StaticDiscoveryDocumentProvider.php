<?php

declare(strict_types=1);

namespace App\Service\Discovery\Document;

use App\ServiceInterface\Discovery\Document\DiscoveryDocumentProviderInterface;
use App\ValueObject\Discovery\DiscoveryDocument;

final class StaticDiscoveryDocumentProvider implements DiscoveryDocumentProviderInterface
{
    public function provide(?string $resourceType = null): array
    {
        $documents = [
            new DiscoveryDocument(
                resourceType: 'project',
                resourceId: 'project-smartresponsor-platform',
                title: 'Smart Responsor platform project',
                body: 'Platform-level project coordinating Symfony components, governance, operation, integration, and business capability growth across the ecosystem.',
                filters: ['status' => 'active', 'visibility' => 'internal'],
                metadata: ['tags' => ['platform', 'symfony', 'ecosystem']],
            ),
            new DiscoveryDocument(
                resourceType: 'project',
                resourceId: 'project-discovery-workspace',
                title: 'Discovering workspace rollout',
                body: 'Workspace-level initiative for building Scout-like application resource discovery with indexing, retrieval, filtering, diagnostics, and management screens.',
                filters: ['status' => 'active', 'visibility' => 'internal'],
                metadata: ['tags' => ['discovery', 'indexing', 'retrieval']],
            ),
            new DiscoveryDocument(
                resourceType: 'offering',
                resourceId: 'offering-ai-automation-audit',
                title: 'AI automation audit offering',
                body: 'Service offering focused on automation review, agent workflow fit, process bottlenecks, and Symfony-oriented modernization opportunities.',
                filters: ['status' => 'active', 'visibility' => 'public'],
                metadata: ['tags' => ['ai', 'automation', 'audit']],
            ),
            new DiscoveryDocument(
                resourceType: 'offering',
                resourceId: 'offering-symfony-modernization',
                title: 'Symfony component modernization offering',
                body: 'Offering for refactoring legacy codebases into coherent Symfony-oriented components with strong manifests, operational surfaces, and growth paths.',
                filters: ['status' => 'active', 'visibility' => 'public'],
                metadata: ['tags' => ['symfony', 'modernization', 'components']],
            ),
            new DiscoveryDocument(
                resourceType: 'document',
                resourceId: 'document-discovery-product-manifest',
                title: 'Discovery product manifesto',
                body: 'Product-facing note explaining that discovery is a resource retrieval and indexing component rather than a generic search engine.',
                filters: ['status' => 'published', 'visibility' => 'internal'],
                metadata: ['tags' => ['manifest', 'product', 'discovery']],
            ),
            new DiscoveryDocument(
                resourceType: 'document',
                resourceId: 'document-backend-guide',
                title: 'Discovery backend guide',
                body: 'Implementation note comparing local SQLite-style discovery flow, scalable Meilisearch flow, and future backend portability expectations.',
                filters: ['status' => 'draft', 'visibility' => 'internal'],
                metadata: ['tags' => ['backend', 'sqlite', 'meilisearch']],
            ),
            new DiscoveryDocument(
                resourceType: 'category',
                resourceId: 'category-automation',
                title: 'Automation category',
                body: 'Category grouping automation-oriented resources, delivery offerings, diagnostic guides, and operational templates.',
                filters: ['status' => 'active', 'visibility' => 'public'],
                metadata: ['tags' => ['automation', 'category']],
            ),
            new DiscoveryDocument(
                resourceType: 'category',
                resourceId: 'category-governance',
                title: 'Governance category',
                body: 'Category for governance-related resources such as canon rules, architecture manifests, diagnostics, and policy-oriented guidance.',
                filters: ['status' => 'active', 'visibility' => 'internal'],
                metadata: ['tags' => ['governance', 'policy', 'canon']],
            ),
        ];

        if ($resourceType === null || $resourceType === '') {
            return $documents;
        }

        return array_values(array_filter(
            $documents,
            static fn (DiscoveryDocument $document): bool => $document->resourceType === $resourceType,
        ));
    }
}
