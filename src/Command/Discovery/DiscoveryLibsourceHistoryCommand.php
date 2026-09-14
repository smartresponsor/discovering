<?php

declare(strict_types=1);

namespace App\Discovering\Command\Discovery;

use App\Discovering\Service\Discovery\Libsource\LibsourceOperatorEventTrailBuilder;
use App\Discovering\ServiceInterface\Discovery\Libsource\Log\LibsourceOperatorEventLogStoreInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a CLI entry point for the discovery libsource history workflow.
 */
#[AsCommand(name: 'app:discovery:libsource:history')]
final class DiscoveryLibsourceHistoryCommand extends Command
{
    public function __construct(
        private readonly LibsourceOperatorEventTrailBuilder $eventTrailBuilder,
        private readonly LibsourceOperatorEventLogStoreInterface $eventLogStore,
    ) {
        parent::__construct();
    }

    /**
     * Executes the discovery libsource history command workflow.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $events = $this->eventTrailBuilder->build();

        $output->writeln(sprintf('Stored action events: %d', count($this->eventLogStore->all())));

        foreach ($events as $event) {
            $output->writeln(sprintf('[%s] %s — %s', $event->level, $event->eventName, $event->summary));
        }

        return Command::SUCCESS;
    }
}
