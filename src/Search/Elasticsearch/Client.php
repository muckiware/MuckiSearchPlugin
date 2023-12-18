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

namespace MuckiSearchPlugin\Search\Elasticsearch;

use Psr\Log\LoggerInterface;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\ClientInterface;
use Elastic\Elasticsearch\Exception\AuthenticationException;

use MuckiSearchPlugin\Services\Settings as PluginSettings;

class Client
{
    public function __construct(
        protected PluginSettings $settings,
        protected LoggerInterface $logger
    )
    {}

    public function getClient(): ?ClientInterface
    {
        try {
            return ClientBuilder::create()
                ->setHosts([$this->settings->getServerConnectionString()])
                ->build();
        } catch (AuthenticationException $exception) {
            $this->logger->error('Problem for to get server connection');
            $this->logger->error($exception->getMessage());
        }

        return null;
    }
}
