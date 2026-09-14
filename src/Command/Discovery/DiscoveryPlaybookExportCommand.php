<?php

declare(strict_types=1);

namespace App\Discovering\Command\Discovery;

use App\Discovering\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a CLI entry point for the discovery playbook export workflow.
 */
#[AsCommand(name: 'app:discovery:playbook:export')]
final class DiscoveryPlaybookExportCommand extends Command
{
    public function __construct(
        private readonly PlaybookFileDiscoverySourceRecordRepository $repository,
    ) {
        parent::__construct();
    }

    /**
     * Configures the Symfony console command metadata, arguments, and help text.
     */
    protected function configure(): void
    {
        $this->addArgument('outputPath', InputArgument::OPTIONAL);
    }

    /**
     * Executes the discovery playbook export command workflow.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $json = $this->repository->exportJson();
        $outputPath = $input->getArgument('outputPath');

        if (is_string($outputPath) && '' !== $outputPath) {
            $directory = dirname($outputPath);

            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            file_put_contents($outputPath, $json);
            $output->writeln(sprintf('Exported playbook source records to %s.', $outputPath));

            return Command::SUCCESS;
        }

        $output->writeln($json);

        return Command::SUCCESS;
    }
}
