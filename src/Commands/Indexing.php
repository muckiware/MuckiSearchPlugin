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
use MuckiSearchPlugin\Indexing\Write as WriteIndex;

#[AsCommand('muckiware:search:indexing')]
class Indexing extends Command
{
    /**
     * @var null
     */
    protected $container = null;

    public function __construct(
        protected PluginSettings $settings,
        private readonly LoggerInterface $logger,
        protected WriteIndex $writeIndex
    ) {

        parent::__construct(self::$defaultName);
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @internal
     */
    public function configure() {
        $this
            ->setDescription('Added shop items into Elasticsearch database')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int {

        $executionStart = microtime(true);

        $output->writeln( 'Starting search indexing');
        $this->logger->info('Starting search indexing', array('mucki','search'));

        $this->writeIndex->doIndexing($output);

        $executionTime = microtime(true) - $executionStart;

        if($executionTime > 60) {
            $output->writeln('Indexing DONE. [Execution: '.(number_format($executionTime/60,2)).' min]');
            $output->write(' ',true);
            $this->logger->info('Indexing DONE. [Execution: '.($executionTime/60).' min]', array('mucki','search'));
        } else {
            $output->writeln('Indexing DONE. [Execution: '.number_format($executionTime, 3).' sec]');
            $output->write(' ',true);
            $this->logger->info('Indexing DONE. [Execution: '.$executionTime.' sec]', array('mucki','search'));
        }

        return self::SUCCESS;
    }
}
