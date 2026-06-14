<?php

declare(strict_types=1);

namespace App\Entity\Discovery;

use App\Repository\Discovery\DiscoveryIndexAliasRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscoveryIndexAliasRepository::class)]
#[ORM\Table(name: 'discovery_index_alias')]
/**
 * Persists the active logical-to-physical index alias used by Discovering rebuild and rollback flows.
 */
final class DiscoveryIndexAliasEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 191)]
    private string $alias = '';

    #[ORM\Column(type: 'string', length: 191)]
    private string $target = '';

    public function alias(): string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }

    public function target(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): void
    {
        $this->target = $target;
    }
}
