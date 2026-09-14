<?php

declare(strict_types=1);

namespace App\Discovering\Entity\Discovery;

use App\Discovering\Repository\Discovery\DiscoveryFeedbackRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscoveryFeedbackRepository::class)]
#[ORM\Table(name: 'discovery_feedback')]
#[ORM\UniqueConstraint(name: 'uniq_discovery_feedback_resource_hit', columns: ['resource', 'hit_id'])]
/**
 * Persists a single feedback signal emitted for a Discovering result or interaction.
 */
final class DiscoveryFeedbackEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 64)]
    private string $resource;

    #[ORM\Column(name: 'hit_id', type: 'string', length: 255)]
    private string $hitId;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => ''])]
    private string $title = '';

    #[ORM\Column(type: 'string', length: 255, options: ['default' => ''])]
    private string $reference = '';

    #[ORM\Column(name: 'click_count', type: 'integer')]
    private int $clickCount = 0;

    #[ORM\Column(name: 'last_clicked_at', type: 'string', length: 64, nullable: true)]
    private ?string $lastClickedAt = null;

    public function __construct(
        string $resource,
        string $hitId,
        string $title = '',
        string $reference = '',
        int $clickCount = 0,
        ?string $lastClickedAt = null,
    ) {
        $this->resource = $resource;
        $this->hitId = $hitId;
        $this->title = $title;
        $this->reference = $reference;
        $this->clickCount = max(0, $clickCount);
        $this->lastClickedAt = $lastClickedAt;
    }

    public function increment(string $title, string $reference, string $lastClickedAt): void
    {
        $this->title = $title;
        $this->reference = $reference;
        $this->clickCount = max(0, $this->clickCount) + 1;
        $this->lastClickedAt = $lastClickedAt;
    }

    public function getClickCount(): int
    {
        return max(0, $this->clickCount);
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getHitId(): string
    {
        return $this->hitId;
    }
}
