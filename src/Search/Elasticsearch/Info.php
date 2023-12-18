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

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Psr\Log\LoggerInterface;

use MuckiSearchPlugin\Search\Elasticsearch\Client as ElasticsearchClient;

class Info
{
    public function __construct(
        protected ElasticsearchClient $elasticsearchClient,
        protected LoggerInterface $logger
    )
    {}

    /**
     *
     */
    public function getInfoAsString(): ?string
    {
        $client = $this->elasticsearchClient->getClient();
        try {
            return $client->info()->asString();
        } catch (ClientResponseException $clientEx) {
            $this->logger->error($clientEx->getMessage());
        } catch (ServerResponseException $resEx) {
            $this->logger->error($resEx->getMessage());
        }
        return null;
    }

    public function getInfoAsObject(): ?object
    {
        $client = $this->elasticsearchClient->getClient();
        try {
            return $client->info()->asObject();
        } catch (ClientResponseException $clientEx) {
            $this->logger->error($clientEx->getMessage());
        } catch (ServerResponseException $resEx) {
            $this->logger->error($resEx->getMessage());
        }
        return null;
    }
}
