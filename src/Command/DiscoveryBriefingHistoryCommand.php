<?php

declare(strict_types=1);

namespace App\Discovering\Command;

use App\Discovering\Builder\Briefing\DiscoveryBriefingOperatorEventTrailBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a CLI entry point for the discovery briefing history workflow.
 */
#[AsCommand(name: 'app:discovery:briefing:history')]
final class DiscoveryBriefingHistoryCommand extends Command
{
    public function __construct(
        private readonly DiscoveryBriefingOperatorEventTrailBuilder $eventTrailBuilder,
    ) {
        parent::__construct();
    }

    /**
     * Executes the discovery briefing history command workflow.
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
