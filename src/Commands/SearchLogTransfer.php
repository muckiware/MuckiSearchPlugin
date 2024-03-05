<?php
/**
 * MuckiSearchPlugin plugin
 *
 *
 * @category   Muckiware
 * @package    MuckiSearch
 * @copyright  Copyright (c) 2023 by Muckiware
 *
 * @author     Muckiware
 *
 */

namespace MuckiSearchPlugin\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;
use Shopware\Core\Framework\Context;

use MuckiSearchPlugin\Services\Settings as PluginSettings;
use MuckiSearchPlugin\Services\SearchTermLog;


#[AsCommand('muckiware:search:log:transfer')]
class SearchLogTransfer extends Command
{
    /**
     * @var null
     */
    protected $container = null;

    public function __construct(
        protected PluginSettings $settings,
        protected LoggerInterface $logger,
        protected SearchTermLog $searchTermLog
    ) {

        parent::__construct(self::$defaultName);
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        if (!$this->container) {
            throw new \LogicException('Cannot retrieve the container from a non-booted kernel.');
        }
        return $this->container;
    }

    /**
     * @internal
     */
    public function configure(): void
    {
        $this->setDescription('Transfer search logs into database');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $serverInfoAsString = $this->searchTermLog->saveSearchLogsIntoDb();
        if($serverInfoAsString) {

            $output->writeln($serverInfoAsString);
            return self::SUCCESS;
        }

        return self::FAILURE;
    }
}
