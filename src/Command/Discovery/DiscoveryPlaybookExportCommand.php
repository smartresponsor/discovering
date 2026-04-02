<?php

declare(strict_types=1);

namespace App\Command\Discovery;

use App\Service\Discovery\Source\Repository\PlaybookFileDiscoverySourceRecordRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InpuArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:discovery:playbook:export')]
final class DiscoveryPlaybookExportCommand extends Command
{
    public function __construct(
        private readonly PlaybookFileDiscoverySourceRecordRepository $repository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('outputPath', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $json = $this->repository->exportJson();
        $outputPath = $input->getArgument('outputPath');

        if (is_string($outputPath) && $outputPath !== '') {
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
