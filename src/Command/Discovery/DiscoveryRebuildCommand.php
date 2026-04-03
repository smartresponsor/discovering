<?php

declare(strict_types=1);

namespace App\Command\Discovery;

use App\Dto\Discovery\ReindexRequest;
use App\Service\Discovery\Operations\DiscoveryOperationLogger;
use App\ServiceInterface\Discovery\Indexer\DiscoveryIndexerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'discovering:rebuild', description: 'Rebuilds discovery indexes.')]
final class DiscoveryRebuildCommand extends Command
{
    public function __construct(
        private readonly DiscoveryIndexerInterface $discoveryIndexer,
        private readonly DiscoveryOperationLogger $operationLogger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('resource', InputArgument::OPTIONAL, 'Resource to rebuild.', 'global');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $resource = (string) $input->getArgument('resource');
        $this->discoveryIndexer->rebuild(new ReindexRequest(resource: $resource, rebuildMode: 'full'));
        $this->operationLogger->recordConsole('discovering.rebuild', context: [
            'resource' => $resource,
            'rebuildMode' => 'full',
        ]);
        $io->success(sprintf('Discovery rebuild completed for "%s".', $resource));

        return Command::SUCCESS;
    }
}
