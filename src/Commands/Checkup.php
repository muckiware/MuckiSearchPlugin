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
use MuckiSearchPlugin\Elasticsearch\Client as ElasticsearchClient;
use MuckiSearchPlugin\Elasticsearch\Info as ElasticsearchInfo;


#[AsCommand('muckiware:search:checkup')]
class Checkup extends Command
{
    /**
     * @var null
     */
    protected $container = null;

    public function __construct(
        protected PluginSettings $settings,
        protected LoggerInterface $logger,
        protected ElasticsearchInfo $elasticsearchInfo
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
            ->setDescription('Checkup Elasticsearch server items')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int {

        $output->writeln($this->elasticsearchInfo->getInfoAsString());

        return self::SUCCESS;
    }
}
