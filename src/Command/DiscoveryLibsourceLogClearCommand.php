<?php

declare(strict_types=1);

namespace App\Discovering\Command;

use App\Discovering\Service\Libsource\DiscoveryLibsourceManagementActionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a CLI entry point for the discovery libsource log clear workflow.
 */
#[AsCommand(name: 'app:discovery:libsource:log:clear')]
final class DiscoveryLibsourceLogClearCommand extends Command
{
    public function __construct(
        private readonly DiscoveryLibsourceManagementActionService $actionService,
    ) {
        parent::__construct();
    }

    /**
     * Executes the discovery libsource log clear command workflow.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->actionService->clearEventLog();
        $output->writeln($result->summary);

        return Command::SUCCESS;
    }
}
