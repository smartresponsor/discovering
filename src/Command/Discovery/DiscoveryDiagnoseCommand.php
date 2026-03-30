<?php
declare(strict_types=1);

namespace App\Command\Discovery;

use App\Dto\Discovery\DiscoveryQuery;
use App\ServiceInterface\Discovery\DiscoveryServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'discovering:diagnose', description: 'Runs a simple discovery diagnostics query.')]
final class DiscoveryDiagnoseCommand extends Command
{
    public function __construct(private readonly DiscoveryServiceInterface $discoveryService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('query', InputArgument::OPTIONAL, 'Discovery query to run.', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $result = $this->discoveryService->discover(new DiscoveryQuery(query: (string) $input->getArgument('query')));
        $io->writeln(sprintf('Total hits: %d', $result->total));
        foreach ($result->hits as $hit) {
            $io->writeln(sprintf('- [%s] %s (%s)', $hit->resource, $hit->title, $hit->reference));
        }
        return Command::SUCCESS;
    }
}
