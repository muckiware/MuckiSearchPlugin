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
use Elastic\Elasticsearch\Response\Elasticsearch;
use Http\Promise\Promise;
use Psr\Log\LoggerInterface;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\ClientInterface;
use Elastic\Elasticsearch\Exception\AuthenticationException;

use MuckiSearchPlugin\Elasticsearch\Client as ElasticsearchClient;

class Search
{
    public function __construct(
        protected ElasticsearchClient $elasticsearchClient,
        protected LoggerInterface $logger
    )
    {}

    /**
     *
     */
    public function searching(array $params): ?array
    {
        try {
            return $this->elasticsearchClient
                ->getClient()
                ->search($params)
                ->asArray();
        } catch (ClientResponseException $clientEx) {
            $this->logger->error($clientEx->getMessage());
        } catch (ServerResponseException $resEx) {
            $this->logger->error($resEx->getMessage());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }
}
