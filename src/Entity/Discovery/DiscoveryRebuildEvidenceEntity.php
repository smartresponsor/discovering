<?php

declare(strict_types=1);

namespace App\Entity\Discovery;

use App\Repository\Discovery\DiscoveryRebuildEvidenceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscoveryRebuildEvidenceRepository::class)]
#[ORM\Table(name: 'discovery_rebuild_evidence')]
/**
 * Persists rebuild evidence records that describe staged-index rebuild decisions and outcomes.
 */
final class DiscoveryRebuildEvidenceEntity
{
    #[ORM\Id]
    #[ORM\Column(name: 'evidence_id', type: 'string', length: 128)]
    private string $evidenceId;

    #[ORM\Column(type: 'string', length: 64)]
    private string $resource;

    #[ORM\Column(name: 'finished_at', type: 'string', length: 64)]
    private string $finishedAt;

    #[ORM\Column(name: 'payload_json', type: 'text')]
    private string $payloadJson;

    public function __construct(string $evidenceId, string $resource, string $finishedAt, string $payloadJson)
    {
        $this->evidenceId = $evidenceId;
        $this->resource = $resource;
        $this->finishedAt = $finishedAt;
        $this->payloadJson = $payloadJson;
    }

    public function getEvidenceId(): string
    {
        return $this->evidenceId;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getFinishedAt(): string
    {
        return $this->finishedAt;
    }

    public function getPayloadJson(): string
    {
        return $this->payloadJson;
    }
}
