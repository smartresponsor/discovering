<?php

declare(strict_types=1);

namespace App\Command\Discovery;

use App\Dto\Discovery\ReindexRequest;
use App\ServiceInterface\Discovery\Indexer\DiscoveryIndexerInterface;
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
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('resourceType', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $request = new ReindexRequest(resourceType: $input->getArgument('resourceType'));
        $this->indexer->rebuild($request);

        $output->writeln('Discovery rebuild finished.');

        return Command::SUCCESS;
    }
}
