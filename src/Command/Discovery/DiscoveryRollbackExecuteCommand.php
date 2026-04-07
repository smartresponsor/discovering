<?php

declare(strict_types=1);

namespace App\Command\Discovery;

use App\Service\Discovery\Operations\DiscoveryOperationLogger;
use App\Service\Discovery\Rollback\DiscoveryRollbackExecutor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Provides a CLI entry point for the discovery rollback execute workflow.
 */
#[AsCommand(name: 'discovering:rollback:execute', description: 'Executes the current discovery rollback alias swap when the plan is ready.')]
final class DiscoveryRollbackExecuteCommand extends Command
{
    public function __construct(
        private readonly DiscoveryRollbackExecutor $rollbackExecutor,
        private readonly DiscoveryOperationLogger $operationLogger,
    ) {
        parent::__construct();
    }

    /**
     * Configures the Symfony console command metadata, arguments, and help text.
     */
    protected function configure(): void
    {
        $this
            ->addOption('current', null, InputOption::VALUE_REQUIRED, 'Expected current evidence id before rollback execution.')
            ->addOption('target', null, InputOption::VALUE_REQUIRED, 'Expected target evidence id before rollback execution.');
    }

    /**
     * Executes the discovery rollback execute command workflow.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $result = $this->rollbackExecutor->execute(
            expectedCurrentEvidenceId: $this->readOption($input, 'current'),
            expectedTargetEvidenceId: $this->readOption($input, 'target'),
        );

        $io->title('Discovery rollback execution');
        $io->definitionList(
            ['Status' => $result->status],
            ['Executed' => $result->executed ? 'yes' : 'no'],
            ['Backend' => $result->backendName],
            ['Current evidence' => $result->currentEvidenceId ?? 'n/a'],
            ['Target evidence' => $result->targetEvidenceId ?? 'n/a'],
            ['Alias' => $result->alias ?? 'n/a'],
            ['Target physical index' => $result->targetPhysicalIndex ?? 'n/a'],
        );

        $this->operationLogger->recordConsole(
            'discovering.rollback.execute',
            status: $result->executed ? 'ok' : 'blocked',
            context: $result->toArray(),
        );

        if ($result->notes !== []) {
            $io->section('Operator notes');
            $io->listing($result->notes);
        }

        return $result->executed ? Command::SUCCESS : Command::FAILURE;
    }

    private function readOption(InputInterface $input, string $name): ?string
    {
        $value = trim((string) $input->getOption($name));

        return $value === '' ? null : $value;
    }
}
