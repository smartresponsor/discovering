<?php

declare(strict_types=1);

namespace App\Command\Discovery;

use App\Service\Discovery\Diagnostics\DiscoveryPlatformDiagnosticsBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:discovery:platform:diagnose')]
final class DiscoveryPlatformDiagnoseCommand extends Command
{
    public function __construct(
        private readonly DiscoveryPlatformDiagnosticsBuilder $diagnosticsBuilder,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $diagnostics = $this->diagnosticsBuilder->build();

        $output->writeln(sprintf('Adapter backend: %s', $diagnostics->backendName));
        $output->writeln(sprintf('Index store backend: %s', $diagnostics->indexStoreBackend));
        $output->writeln(sprintf('Staged rebuild supported: %s', $diagnostics->stagedRebuildSupported ? 'yes' : 'no'));
        $output->writeln(sprintf('Shared state configured: %s', $diagnostics->sharedStateConfigured ? 'yes' : 'no'));
        $output->writeln(sprintf('Distributed ready: %s', $diagnostics->distributedReady ? 'yes' : 'no'));
        $output->writeln(sprintf('Rollback status: %s', $diagnostics->rollbackStatus));
        $output->writeln(sprintf('Rollback ready: %s', $diagnostics->rollbackReady ? 'yes' : 'no'));
        $output->writeln(sprintf('Multi-replica-ready stores: %d', $diagnostics->coordinationReadyStoreCount));

        $output->writeln('Store backends:');
        foreach ($diagnostics->storeBackends as $name => $backend) {
            $output->writeln(sprintf('- %s: %s', $name, $backend));
        }

        $output->writeln('Blocking stores:');
        if ($diagnostics->blockingStores === []) {
            $output->writeln('- none');
        } else {
            foreach ($diagnostics->blockingStores as $storeName) {
                $output->writeln(sprintf('- %s', $storeName));
            }
        }

        $output->writeln('Notes:');
        foreach ($diagnostics->notes as $note) {
            $output->writeln(sprintf('- %s', $note));
        }

        return Command::SUCCESS;
    }
}
