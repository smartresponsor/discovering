<?php

declare(strict_types=1);

namespace App\Command\Discovery;

use App\Service\Discovery\Operations\DiscoveryOperationLogger;
use App\Service\Discovery\Rebuild\DiscoveryRollbackPlanBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Provides a CLI entry point for the discovery rollback plan workflow.
 */
#[AsCommand(name: 'app:discovery:rollback:plan', description: 'Builds the current discovery rollback plan from rebuild evidence.', aliases: ['discovering:rollback:plan'])]
final class DiscoveryRollbackPlanCommand extends Command
{
    public function __construct(
        private readonly DiscoveryRollbackPlanBuilder $rollbackPlanBuilder,
        private readonly DiscoveryOperationLogger $operationLogger,
    ) {
        parent::__construct();
    }

    /**
     * Executes the discovery rollback plan command workflow.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $plan = $this->rollbackPlanBuilder->build();

        $io->title('Discovery rollback plan');
        $io->definitionList(
            ['Status' => $plan->status],
            ['Rollback ready' => $plan->rollbackReady ? 'yes' : 'no'],
            ['Current evidence' => $plan->currentEvidenceId ?? 'n/a'],
            ['Previous evidence' => $plan->previousEvidenceId ?? 'n/a'],
            ['Current physical index' => $plan->currentPhysicalIndex ?? 'n/a'],
            ['Rollback target' => $plan->rollbackTargetPhysicalIndex ?? 'n/a'],
            ['Recommended command' => $plan->recommendedCommand ?? 'n/a'],
        );

        $this->operationLogger->recordConsole('discovering.rollback.plan', context: $plan->toArray());

        if ([] !== $plan->notes) {
            $io->section('Operator notes');
            $io->listing($plan->notes);
        }

        return Command::SUCCESS;
    }
}
