<?php

declare(strict_types=1);

namespace App\Command\Discovery;

use App\Service\Discovery\Libsource\LibsourceManagementActionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:discovery:libsource:log:clear')]
final class DiscoveryLibsourceLogClearCommand extends Command
{
    public function __construct(
        private readonly LibsourceManagementActionService $actionService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->actionService->clearEventLog();
        $output->writeln($result->summary);

        return Command::SUCCESS;
    }
}
