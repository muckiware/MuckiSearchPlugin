<?php

namespace MuckiSearchPlugin\Services;

use Psr\Log\LoggerInterface;
use Shopware\Core\Content\ImportExport\Struct\Progress;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class CliOutput
{
    final const PROGRESS_BAR_OFFSET = 0;

    public function __construct(
        protected SystemConfigService $_config,
        protected LoggerInterface $logger
    ){}

    public function prepareProductProgressBar(
        Progress $progress,
        string $languageName,
        int $totalCounter,
        OutputInterface $cliOutput
    ): ProgressBar
    {
        if(!$progress->getTotal()) {
            $progressTotal = 0;
        } else {
            $progressTotal = $progress->getTotal();
        }
        $progressBar = new ProgressBar($cliOutput, $totalCounter);
        $progressBar->setMaxSteps($progressTotal);
        $progressBar->setFormat('[%bar%] %current%/%max% indexing products for '.$languageName."\n");
        $progressBar->start();

        return $progressBar;
    }

    public function prepareProductProgress(int $totalCounter): Progress
    {
        $progress = new Progress(Uuid::randomHex(), Progress::STATE_PROGRESS, self::PROGRESS_BAR_OFFSET);
        $progress->setTotal($totalCounter);
        $progress->setOffset(self::PROGRESS_BAR_OFFSET);

        return $progress;
    }

    public function prepareCategoryProgressBar(
        Progress $progress,
        string $languageName,
        int $totalCounter,
        OutputInterface $cliOutput
    ): ProgressBar
    {
        if(!$progress->getTotal()) {
            $progressTotal = 0;
        } else {
            $progressTotal = $progress->getTotal();
        }
        $progressBar = new ProgressBar($cliOutput, $totalCounter);
        $progressBar->setMaxSteps($progressTotal);
        $progressBar->setFormat('[%bar%] %current%/%max% indexing categories for '.$languageName."\n");
        $progressBar->start();

        return $progressBar;
    }

    public function prepareCategoryProgress(int $totalCounter): Progress
    {
        $progress = new Progress(Uuid::randomHex(), Progress::STATE_PROGRESS, self::PROGRESS_BAR_OFFSET);
        $progress->setTotal($totalCounter);
        $progress->setOffset(self::PROGRESS_BAR_OFFSET);

        return $progress;
    }

    public function prepareIndexStructureProgressBar(
        Progress $progress,
        int $totalCounter,
        OutputInterface $cliOutput
    ): ProgressBar
    {
        if(!$progress->getTotal()) {
            $progressTotal = 0;
        } else {
            $progressTotal = $progress->getTotal();
        }

        $progressBar = new ProgressBar($cliOutput, $totalCounter);
        $progressBar->setMaxSteps($progressTotal);
        $progressBar->setFormat('[%bar%] %current%/%max% Index Structure');
        $cliOutput->write('',true);
        $progressBar->start();

        return $progressBar;
    }

    public function prepareIndexStructureProgress(int $totalCounter): Progress
    {
        $progress = new Progress(Uuid::randomHex(), Progress::STATE_PROGRESS, self::PROGRESS_BAR_OFFSET);
        $progress->setTotal($totalCounter);
        $progress->setOffset(self::PROGRESS_BAR_OFFSET);

        return $progress;
    }

    public function printCliOutput(OutputInterface $cliOutput, string $message = ''): void
    {
        if($message !== '') {
            $cliOutput->writeln($message);
        }
    }
}

