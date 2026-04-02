<?php

declare(strict_types=1);

namespace App\Command\Discovery;

use App\Service\Discovery\Briefing\BriefingOperatorEventTrailBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:discovery:briefing:history')]
final class DiscoveryBriefingHistoryCommand extends Command
{
    public function __construct(
        private readonly BriefingOperatorEventTrailBuilder $eventTrailBuilder,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $events = $this->eventTrailBuilder->build();

        foreach ($events as $event) {
            $output->writeln(sprintf('[%s] %s — %s', $event->level, $event->eventName, $event->summary));
        }

        return Command::SUCCESS;
    }
}
