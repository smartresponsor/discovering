<?php

declare(strict_types=1);

namespace App\Discovering\Entity\Discovery;

use App\Discovering\Repository\Discovery\DiscoverySearchLogRepository;
use App\Objecting\EntityTrait\Embeddable\ObjectAuditEmbeddableTrait;
use App\Objecting\EntityTrait\Embeddable\ObjectIdentityEmbeddableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscoverySearchLogRepository::class)]
#[ORM\Table(name: 'discovery_search_log')]
#[ORM\Index(name: 'idx_discovery_search_log_term', columns: ['term'])]
#[ORM\Index(name: 'idx_discovery_search_log_customer_reference', columns: ['customer_reference'])]
/**
 * Records submitted discovery/search terms without duplicating Objecting system fields.
 */
final class DiscoverySearchLogEntity
{
    use ObjectIdentityEmbeddableTrait;
    use ObjectAuditEmbeddableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 128)]
    private string $term;

    #[ORM\Column(name: 'customer_reference', type: 'string', length: 64, nullable: true)]
    private ?string $customerReference = null;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $filters = null;

    /**
     * @param array<string, mixed>|null $filters
     */
    public function __construct(string $term, ?string $customerReference = null, ?array $filters = null)
    {
        $this->initializeObjectIdentity();
        $this->initializeObjectAudit();
        $this->term = $term;
        $this->customerReference = $customerReference;
        $this->filters = $filters;
    }

    public function getTerm(): string
    {
        return $this->term;
    }

    public function setTerm(string $term): void
    {
        $this->term = $term;
        $this->touchModified();
    }

    public function getCustomerReference(): ?string
    {
        return $this->customerReference;
    }

    public function setCustomerReference(?string $customerReference): void
    {
        $this->customerReference = $customerReference;
        $this->touchModified();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getFilters(): ?array
    {
        return $this->filters;
    }

    /**
     * @param array<string, mixed>|null $filters
     */
    public function setFilters(?array $filters): void
    {
        $this->filters = $filters;
        $this->touchModified();
    }
}
