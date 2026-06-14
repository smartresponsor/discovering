<?php

declare(strict_types=1);

namespace App\Service\Discovery\Source\Repository;

use App\ServiceInterface\Discovery\Source\Repository\DiscoverySourceRecordRepositoryInterface; /**
 * Resolves and serves discovery source repository entries for the discovery component.
 */
final class DiscoverySourceRepositoryRegistry
{
    /**
     * @param iterable<DiscoverySourceRecordRepositoryInterface> $repositories
     */
    public function __construct(
        private readonly iterable $repositories,
    ) {
    }

    /**
     * @return list<DiscoverySourceRecordRepositoryInterface>
     */
    public function all(): array
    {
        return is_array($this->repositories) ? $this->repositories : iterator_to_array($this->repositories, false);
    }

    /**
     * Returns the by source nameEntity value exposed by this service.
     */
    public function getBySourceName(string $sourceName): DiscoverySourceRecordRepositoryInterface
    {
        foreach ($this->all() as $repository) {
            if ($repository->getSourceName() === $sourceName) {
                return $repository;
            }
        }

        throw new \RuntimeException(sprintf('No source repository found for %s.', $sourceName));
    }
}
