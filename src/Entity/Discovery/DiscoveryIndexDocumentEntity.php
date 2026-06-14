<?php

declare(strict_types=1);

namespace App\Entity\Discovery;

use App\Repository\Discovery\DiscoveryIndexDocumentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscoveryIndexDocumentRepository::class)]
#[ORM\Table(name: 'discovery_index_document')]
#[ORM\UniqueConstraint(name: 'uniq_discovery_index_document', columns: ['index_name', 'document_id'])]
/**
 * Persists indexed Discovering document metadata and payload references for Doctrine-backed indexing state.
 */
final class DiscoveryIndexDocumentEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'index_name', type: 'string', length: 191)]
    private string $indexName = '';

    #[ORM\Column(name: 'document_id', type: 'string', length: 191)]
    private string $documentId = '';

    #[ORM\Column(type: 'string', length: 255)]
    private string $title = '';

    #[ORM\Column(type: 'string', length: 191)]
    private string $resource = '';

    #[ORM\Column(type: 'string', length: 255)]
    private string $reference = '';

    #[ORM\Column(type: 'string', length: 64)]
    private string $status = '';

    #[ORM\Column(type: 'text')]
    private string $content = '';

    #[ORM\Column(name: 'updated_at', type: 'string', length: 64)]
    private string $updatedAt = '';

    public function id(): ?int
    {
        return $this->id;
    }

    public function indexName(): string
    {
        return $this->indexName;
    }

    public function setIndexName(string $indexName): void
    {
        $this->indexName = $indexName;
    }

    public function documentId(): string
    {
        return $this->documentId;
    }

    public function setDocumentId(string $documentId): void
    {
        $this->documentId = $documentId;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function resource(): string
    {
        return $this->resource;
    }

    public function setResource(string $resource): void
    {
        $this->resource = $resource;
    }

    public function reference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): void
    {
        $this->reference = $reference;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function updatedAt(): string
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
