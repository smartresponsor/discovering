<?php

declare(strict_types=1);

namespace App\Discovering\Command\Discovery;

use App\Discovering\Service\Discovery\Libsource\LibsourceManagementActionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a CLI entry point for the discovery libsource inspect workflow.
 */
#[AsCommand(name: 'app:discovery:libsource:inspect')]
final class DiscoveryLibsourceInspectCommand extends Command
{
    public function __construct(
        private readonly LibsourceManagementActionService $actionService,
    ) {
        parent::__construct();
    }

    /**
     * Configures the Symfony console command metadata, arguments, and help text.
     */
    protected function configure(): void
    {
        $this->addArgument('sourceName', InputArgument::REQUIRED);
    }

    /**
     * Executes the discovery libsource inspect command workflow.
     */
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
