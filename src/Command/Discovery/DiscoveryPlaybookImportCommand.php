<?php

declare(strict_types=1);

namespace App\Command\Discovery;

use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a CLI entry point for the discovery playbook import workflow.
 */
#[AsCommand(name: 'app:discovery:playbook:import')]
final class DiscoveryPlaybookImportCommand extends Command
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
        $this->addArgument('inputPath', InputArgument::REQUIRED);
    }

    /**
     * Executes the discovery playbook import command workflow.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputPath = $input->getArgument('inputPath');

        if (!is_string($inputPath) || '' === $inputPath) {
            $output->writeln('Input path is required.');

            return Command::INVALID;
        }

        try {
            $importedCount = $this->repository->importFile($inputPath);
        } catch (\Throwable $e) {
            $output->writeln(sprintf('Failed to import playbook records: %s', $e->getMessage()));

            return Command::FAILURE;
        }

        $output->writeln(sprintf(
            'Imported %d playbook records into %s.',
            $importedCount,
            $this->repository->getStoragePath(),
        ));

        return Command::SUCCESS;
    }
}
