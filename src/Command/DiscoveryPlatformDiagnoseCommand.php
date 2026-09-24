<?php

declare(strict_types=1);

namespace App\Discovering\Command;

use App\Discovering\Builder\Diagnostics\DiscoveryBackendReachabilityBuilder;
use App\Discovering\Builder\Diagnostics\DiscoveryPlatformDiagnosticsBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a CLI entry point for the discovery platform diagnose workflow.
 */
#[AsCommand(name: 'app:discovery:platform:diagnose')]
final class DiscoveryPlatformDiagnoseCommand extends Command
{
    public function __construct(
        private readonly DiscoveryPlatformDiagnosticsBuilder $diagnosticsBuilder,
        private readonly DiscoveryBackendReachabilityBuilder $probeBuilder,
    ) {
        parent::__construct();
    }

    /**
     * Executes the discovery platform diagnose command workflow.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $diagnostics = $this->diagnosticsBuilder->build();

        $output->writeln(sprintf('Discovery backend: %s', $diagnostics->backendName));
        $output->writeln(sprintf('Index store backend: %s', $diagnostics->indexStoreBackend));
        $output->writeln(sprintf('Staged rebuild supported: %s', $diagnostics->stagedRebuildSupported ? 'yes' : 'no'));
        $output->writeln(sprintf('Shared state configured: %s', $diagnostics->sharedStateConfigured ? 'yes' : 'no'));
        $output->writeln(sprintf('Distributed ready: %s', $diagnostics->distributedReady ? 'yes' : 'no'));
        $output->writeln(sprintf('Rollback status: %s', $diagnostics->rollbackStatus));
        $output->writeln(sprintf('Rollback ready: %s', $diagnostics->rollbackReady ? 'yes' : 'no'));
        $output->writeln(sprintf('Multi-replica-ready stores: %d', $diagnostics->coordinationReadyStoreCount));
        $output->writeln(sprintf('Posture status: %s', $diagnostics->postureStatus));
        $output->writeln(sprintf('Risk level: %s', $diagnostics->riskLevel));
        $output->writeln(sprintf('Recommended action: %s', $diagnostics->recommendedAction));

        $output->writeln('Store backends:');
        foreach ($diagnostics->storeBackends as $nameEntity => $backend) {
            $output->writeln(sprintf('- %s: %s', $nameEntity, $backend));
        }

        $output->writeln('Blocking stores:');
        if ([] === $diagnostics->blockingStores) {
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

        $probeReport = $this->probeBuilder->build();
        $output->writeln('Backend probes:');
        $output->writeln(sprintf('- performed: %d', $probeReport->performedProbeCount));
        $output->writeln(sprintf('- reachable: %d', $probeReport->reachableProbeCount));
        $output->writeln(sprintf('- failing: %d', $probeReport->failingProbeCount));
        $output->writeln(sprintf('- skipped: %d', $probeReport->skippedProbeCount));
        $output->writeln(sprintf('- overall status: %s', $probeReport->overallStatus));
        $output->writeln(sprintf('- recommended action: %s', $probeReport->recommendedAction));

        return 0 === $probeReport->failingProbeCount ? Command::SUCCESS : Command::FAILURE;
    }
}
