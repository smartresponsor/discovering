<?php

declare(strict_types=1);

namespace App\Command\Discovery;

use App\Service\Discovery\Libsource\LibsourceManagementActionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:discovery:libsource:inspect')]
final class DiscoveryLibsourceInspectCommand extends Command
{
    public function __construct(
        private readonly LibsourceManagementActionService $actionService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('sourceName', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sourceName = (string) $input->getArgument('sourceName');
        $result = $this->actionService->inspect($sourceName);

        $output->writeln(sprintf('Action: %s', $result->actionName));
        $output->writeln(sprintf('Summary: %s', $result->summary));
        $output->writeln(json_encode($result->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}');

        return Command::SUCCESS;
    }
}
