<?php

declare(strict_types=1);

namespace App\Discovering\Command\Discovery;

use App\Discovering\Dto\Discovery\ReindexRequest;
use App\Discovering\Service\Discovery\Operations\DiscoveryOperationLogger;
use App\Discovering\ServiceInterface\Discovery\Indexer\DiscoveryIndexerInterface;
use App\Discovering\ServiceInterface\Discovery\Rebuild\DiscoveryRebuildEvidenceStoreInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Provides a CLI entry point for the discovery rebuild workflow.
 */
#[AsCommand(name: 'app:discovery:rebuild', description: 'Rebuilds discovery indexes.', aliases: ['discovering:rebuild'])]
final class DiscoveryRebuildCommand extends Command
{
    public function __construct(
        private readonly DiscoveryIndexerInterface $discoveryIndexer,
        private readonly DiscoveryRebuildEvidenceStoreInterface $rebuildEvidenceStore,
        private readonly DiscoveryOperationLogger $operationLogger,
    ) {
        parent::__construct();
    }

    /**
     * Configures the Symfony console command metadata, arguments, and help text.
     */
    protected function configure(): void
    {
        $this->addArgument('resource', InputArgument::OPTIONAL, 'Resource to rebuild.', 'global');
        $this->addOption('deployment-mode', null, InputOption::VALUE_REQUIRED, 'Deployment mode (auto, in_place, staged_alias_swap).', 'auto');
    }

    /**
     * Executes the discovery rebuild command workflow.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $resource = (string) $input->getArgument('resource');
        $deploymentMode = (string) $input->getOption('deployment-mode');
        $summary = $this->discoveryIndexer->rebuild(new ReindexRequest(
            resource: $resource,
            rebuildMode: 'full',
            deploymentMode: $deploymentMode,
        ));
        $this->rebuildEvidenceStore->append($summary);
        $this->operationLogger->recordConsole('discovering.rebuild', context: [
            'resource' => $resource,
            'rebuildMode' => 'full',
            'deploymentMode' => $summary->deploymentMode,
            'requestedDeploymentMode' => $deploymentMode,
            'evidenceId' => $summary->evidenceId,
            'indexedDocumentCount' => $summary->indexedDocumentCount,
            'candidateDocumentCount' => $summary->candidateDocumentCount,
            'zeroDowntimeReady' => $summary->zeroDowntimeReady,
            'aliasSwapApplied' => $summary->aliasSwapApplied,
            'stagedIndexes' => $summary->stagedIndexes,
        ]);

        $io->success(sprintf(
            'Discovery rebuild completed for "%s" via %s. Evidence: %s; indexed %d of %d candidates.',
            $resource,
            $summary->deploymentMode,
            $summary->evidenceId,
            $summary->indexedDocumentCount,
            $summary->candidateDocumentCount,
        ));

        return Command::SUCCESS;
    }
}
