<?php

declare(strict_types=1);

namespace App\Command\Discovery;

use App\Service\Discovery\Diagnostics\DiscoveryBackendReachabilityBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a CLI entry point for the discovery platform probe workflow.
 */
#[AsCommand(name: 'app:discovery:platform:probe')]
final class DiscoveryPlatformProbeCommand extends Command
{
    public function __construct(
        private readonly DiscoveryBackendReachabilityBuilder $probeBuilder,
    ) {
        parent::__construct();
    }

    /**
     * Executes the discovery platform probe command workflow.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $report = $this->probeBuilder->build();

        $output->writeln(sprintf('Checked at: %s', $report->checkedAt));
        $output->writeln(sprintf('Performed probes: %d', $report->performedProbeCount));
        $output->writeln(sprintf('Reachable probes: %d', $report->reachableProbeCount));
        $output->writeln(sprintf('Failing probes: %d', $report->failingProbeCount));
        $output->writeln(sprintf('Skipped probes: %d', $report->skippedProbeCount));
        $output->writeln(sprintf('Overall status: %s', $report->overallStatus));
        $output->writeln(sprintf('Recommended action: %s', $report->recommendedAction));
        $output->writeln('Probes:');

        foreach ($report->probes as $probe) {
            $output->writeln(sprintf('- %s [%s] %s => %s', $probe->nameEntity, $probe->backend, $probe->target, $probe->status));
            foreach ($probe->details as $detail) {
                $output->writeln(sprintf('  · %s', $detail));
            }
        }

        if ([] !== $report->failingProbeNames) {
            $output->writeln(sprintf('Failing probes: %s', implode(', ', $report->failingProbeNames)));
        }

        if ([] !== $report->notes) {
            $output->writeln('Notes:');
            foreach ($report->notes as $note) {
                $output->writeln(sprintf('- %s', $note));
            }
        }

        return 0 === $report->failingProbeCount ? Command::SUCCESS : Command::FAILURE;
    }
}
