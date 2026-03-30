<?php

declare(strict_types=1);

namespace App\Command\Discovery;

use App\Dto\Discovery\ReindexRequest;
use App\ServiceInterface\Discovery\Indexer\DiscoveryIndexerInterface;
use App\ServiceInterface\Discovery\Overview\DiscoveryOverviewServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:discovery:rebuild')]
final class DiscoveryRebuildCommand extends Command
{
    public function __construct(
        private readonly DiscoveryIndexerInterface $indexer,
        private readonly DiscoveryOverviewServiceInterface $overviewService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('resourceType', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $resourceType = $input->getArgument('resourceType');
        $request = new ReindexRequest(resourceType: is_string($resourceType) ? $resourceType : null);
        $this->indexer->rebuild($request);

        $overview = $this->overviewService->buildOverview();

        $output->writeln(sprintf('Discovery rebuild finished using backend %s.', $overview->backendName));
        $output->writeln(sprintf('Total seeded documents available: %d', $overview->totalDocuments));

        return Command::SUCCESS;
    }
}
