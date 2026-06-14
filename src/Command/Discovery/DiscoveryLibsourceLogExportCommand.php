<?php

declare(strict_types=1);

namespace App\Command\Discovery;

use App\Dto\Discovery\LibsourceEventLogQuery;
use App\Dto\Discovery\LibsourceOperatorEvent;
use App\Service\Discovery\Libsource\LibsourceEventLogSurfaceBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a CLI entry point for the discovery libsource log export workflow.
 */
#[AsCommand(name: 'app:discovery:libsource:log:export')]
final class DiscoveryLibsourceLogExportCommand extends Command
{
    public function __construct(
        private readonly LibsourceEventLogSurfaceBuilder $surfaceBuilder,
    ) {
        parent::__construct();
    }

    /**
     * Configures the Symfony console command metadata, arguments, and help text.
     */
    protected function configure(): void
    {
        $this->addOption('preset', null, InputOption::VALUE_REQUIRED);
        $this->addOption('search', null, InputOption::VALUE_REQUIRED);
        $this->addOption('level', null, InputOption::VALUE_REQUIRED);
        $this->addOption('page', null, InputOption::VALUE_REQUIRED, default: '1');
        $this->addOption('per-page', null, InputOption::VALUE_REQUIRED, default: '10');
    }

    /**
     * Executes the discovery libsource log export command workflow.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $surface = $this->surfaceBuilder->build(new LibsourceEventLogQuery(
            preset: ($input->getOption('preset') ?: null),
            search: ($input->getOption('search') ?: null),
            level: ($input->getOption('level') ?: null),
            page: max(1, (int) $input->getOption('page')),
            perPage: max(1, (int) $input->getOption('per-page')),
        ));

        $payload = [
            'backendClass' => $surface->backendClass,
            'totalEvents' => $surface->totalEvents,
            'filteredTotalEvents' => $surface->filteredTotalEvents,
            'availablePresets' => $surface->availablePresets,
            'activePreset' => $surface->activePreset,
            'activeLevel' => $surface->activeLevel,
            'activeSearch' => $surface->activeSearch,
            'page' => $surface->page,
            'perPage' => $surface->perPage,
            'totalPages' => $surface->totalPages,
            'events' => array_map(
                static fn (LibsourceOperatorEvent $event): array => [
                    'eventName' => $event->eventName,
                    'level' => $event->level,
                    'summary' => $event->summary,
                    'context' => $event->context,
                ],
                $surface->events,
            ),
        ];

        $output->writeln(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}');

        return Command::SUCCESS;
    }
}
