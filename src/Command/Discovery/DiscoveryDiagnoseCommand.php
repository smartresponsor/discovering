<?php

declare(strict_types=1);

namespace App\Command\Discovery;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:discovery:diagnose')]
final class DiscoveryDiagnoseCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Discovery diagnosis placeholder.');

        return Command::SUCCESS;
    }
}
