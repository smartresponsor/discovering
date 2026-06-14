<?php

declare(strict_types=1);

namespace App\Entity\Discovery;

use App\Repository\Discovery\DiscoveryRateLimitBucketRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscoveryRateLimitBucketRepository::class)]
#[ORM\Table(name: 'discovery_rate_limit_bucket')]
#[ORM\UniqueConstraint(name: 'uniq_discovery_rate_limit_bucket', columns: ['bucket'])]
/**
 * Persists rate-limit bucket counters used by Doctrine-backed Discovering request throttling.
 */
final class DiscoveryRateLimitBucketEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $bucket;

    #[ORM\Column(name: 'bucket_count', type: 'integer')]
    private int $bucketCount = 0;

    #[ORM\Column(name: 'reset_at', type: 'integer')]
    private int $resetAt = 0;

    public function __construct(string $bucket, int $bucketCount, int $resetAt)
    {
        $this->bucket = $bucket;
        $this->bucketCount = max(0, $bucketCount);
        $this->resetAt = $resetAt;
    }

    public function increment(int $resetAt): void
    {
        $this->bucketCount = max(0, $this->bucketCount) + 1;
        $this->resetAt = $resetAt;
    }

    public function reset(int $resetAt): void
    {
        $this->bucketCount = 1;
        $this->resetAt = $resetAt;
    }

    public function getBucketCount(): int
    {
        return max(0, $this->bucketCount);
    }

    public function getResetAt(): int
    {
        return $this->resetAt;
    }

    public function getBucket(): string
    {
        return $this->bucket;
    }
}
