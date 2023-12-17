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
use Elastic\Elasticsearch\ClientBuilder;

use MuckiSearchPlugin\Services\Settings as PluginSettings;

#[AsCommand('muckiware:search:checkup')]
class Checkup extends Command
{
    /**
     * @var null
     */
    protected $container = null;

    public function __construct(
        protected PluginSettings $settings,
        private readonly LoggerInterface $logger
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

        $client = ClientBuilder::create()
            ->setHosts(['elasticsearch:9200'])
            ->build();

        // Info API
        $response = $client->info()['version'];



        return self::SUCCESS;
    }
}
