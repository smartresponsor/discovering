<?php

declare(strict_types=1);

namespace App\Command\Discovery;

use App\ServiceInterface\Discovery\Overview\DiscoveryOverviewServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:discovery:diagnose')]
final class DiscoveryDiagnoseCommand extends Command
{
    public function __construct(
        private readonly DiscoveryOverviewServiceInterface $overviewService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $overview = $this->overviewService->buildOverview();

        $output->writeln(sprintf('Backend: %s', $overview->backendName));
        $output->writeln(sprintf('Total documents: %d', $overview->totalDocuments));

        foreach ($overview->countsByResourceType as $resourceType => $count) {
            $output->writeln(sprintf('- %s: %d', $resourceType, $count));
        }

        return Command::SUCCESS;
    }
}
