<?php

declare(strict_types=1);

namespace App\Command\Discovery;

use App\Service\Discovery\Libsource\LibsourceDiagnosticSurfaceBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:discovery:libsource:diagnose')]
final class DiscoveryLibsourceDiagnoseCommand extends Command
{
    public function __construct(
        private readonly LibsourceDiagnosticSurfaceBuilder $surfaceBuilder,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $surface = $this->surfaceBuilder->build();

        $output->writeln(sprintf('Total sources: %d', count($surface->entries)));

        foreach ($surface->entries as $entry) {
            $output->writeln(sprintf(
                '- %s [%s] provider=%s repository=%s records=%d samples=%s',
                $entry->sourceName,
                $entry->resourceType,
                $entry->providerClass,
                $entry->repositoryClass,
                $entry->recordCount,
                implode(',', $entry->sampleResourceIds),
            ));
        }

        return Command::SUCCESS;
    }
}
