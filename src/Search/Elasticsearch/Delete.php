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
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Http\Promise\Promise;
use Psr\Log\LoggerInterface;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\ClientInterface;
use Elastic\Elasticsearch\Exception\AuthenticationException;

use MuckiSearchPlugin\Elasticsearch\Client as ElasticsearchClient;

class Delete
{
    public function __construct(
        protected ElasticsearchClient $elasticsearchClient,
        protected LoggerInterface $logger
    )
    {}

    /**
     *
     */
    public function searching(array $params): bool
    {
        $response['acknowledge'] = 0;
        try {
            $response = $this->elasticsearchClient
                ->getClient()
                ->delete($params);
        } catch (ClientResponseException $clientEx) {

            if ($clientEx->getCode() === 404) {
                $this->logger->warning('item not found');
            }
            $this->logger->error($clientEx->getMessage());
        } catch (MissingParameterException $exMiss) {
            $this->logger->error($exMiss->getMessage());
        } catch (ServerResponseException $exRes) {
            $this->logger->error($exRes->getMessage());
        }

        if($response['acknowledge'] === 1) {
            return true;
        }

        return false;
    }
}
