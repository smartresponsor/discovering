<?php

declare(strict_types=1);

namespace App\Command\Discovery;

use App\Dto\Discovery\ReindexRequest;
use App\Service\Discovery\Operations\DiscoveryOperationLogger;
use App\Service\Discovery\Rebuild\DiscoveryRebuildEvidenceStoreInterface;
use App\ServiceInterface\Discovery\Indexer\DiscoveryIndexerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'discovering:rebuild', description: 'Rebuilds discovery indexes.')]
final class DiscoveryRebuildCommand extends Command
{
    public function __construct(
        private readonly DiscoveryIndexerInterface $discoveryIndexer,
        private readonly DiscoveryRebuildEvidenceStoreInterface $rebuildEvidenceStore,
        private readonly DiscoveryOperationLogger $operationLogger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('resource', InputArgument::OPTIONAL, 'Resource to rebuild.', 'global');
        $this->addOption('deployment-mode', null, InputOption::VALUE_REQUIRED, 'Deployment mode (auto, in_place, staged_alias_swap).', 'auto');
    }

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
