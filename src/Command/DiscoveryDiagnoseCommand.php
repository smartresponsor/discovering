<?php

declare(strict_types=1);

namespace App\Discovering\Command;

use App\Discovering\ServiceInterface\Overview\DiscoveryOverviewServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a CLI entry point for the discovery diagnose workflow.
 */
#[AsCommand(name: 'app:discovery:diagnose')]
final class DiscoveryDiagnoseCommand extends Command
{
    public function __construct(
        private readonly DiscoveryOverviewServiceInterface $overviewService,
    ) {
        parent::__construct();
    }

    /**
     * Executes the discovery diagnose command workflow.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $overview = $this->overviewService->buildOverview();

        $output->writeln(sprintf('Backend: %s', $overview->backendName));
        $output->writeln(sprintf('Total documents: %d', $overview->totalDocuments));
        $output->writeln('Counts by resource type:');

        foreach ($overview->countsByResourceType as $resourceType => $count) {
            $output->writeln(sprintf('- %s: %d', $resourceType, $count));
        }

        $output->writeln('Counts by source:');

        foreach ($overview->countsBySourceName as $sourceName => $count) {
            $output->writeln(sprintf('- %s: %d', $sourceName, $count));
        }

        return Command::SUCCESS;
    }
}
