<?php

declare(strict_types=1);

namespace App\Command\Discovery;

use App\Service\Discovery\Playbook\PlaybookOperatorEventTrailBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a CLI entry point for the discovery playbook history workflow.
 */
#[AsCommand(name: 'app:discovery:playbook:history')]
final class DiscoveryPlaybookHistoryCommand extends Command
{
    public function __construct(
        private readonly PlaybookOperatorEventTrailBuilder $eventTrailBuilder,
    ) {
        parent::__construct();
    }

    /**
     * Executes the discovery playbook history command workflow.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $events = $this->eventTrailBuilder->build();

        foreach ($events as $event) {
            $output->writeln(sprintf('[%s] %s — %s', $event->level, $event->eventName, $event->summary));
        }

        return Command::SUCCESS;
    }
}
