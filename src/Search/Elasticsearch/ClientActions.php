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

use MuckiSearchPlugin\Services\Settings as PluginSettings;
use MuckiSearchPlugin\Entities\CreateIndicesBody;

class ClientActions
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

    public function searching(array $params): ?array
    {
        try {
            return $this->getClient()->search($params)->asArray();
        } catch (ClientResponseException $clientEx) {
            $this->logger->error($clientEx->getMessage());
        } catch (ServerResponseException $resEx) {
            $this->logger->error($resEx->getMessage());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }

    public function getServerInfoAsString(): ?string
    {
        try {
            return $this->getClient()->info()->asString();
        } catch (ClientResponseException $clientEx) {
            $this->logger->error($clientEx->getMessage());
        } catch (ServerResponseException $resEx) {
            $this->logger->error($resEx->getMessage());
        }
        return null;
    }

    public function getServerInfoAsObject(): ?object
    {
        try {
            return $this->getClient()->info()->asObject();
        } catch (ClientResponseException $clientEx) {
            $this->logger->error($clientEx->getMessage());
        } catch (ServerResponseException $resEx) {
            $this->logger->error($resEx->getMessage());
        }
        return null;
    }

    public function indexing(array $params): ?array
    {
        try {
            return $this->getClient()->index($params)->asArray();
        } catch (ClientResponseException $clientEx) {
            $this->logger->error($clientEx->getMessage());
        } catch (ServerResponseException $resEx) {
            $this->logger->error($resEx->getMessage());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }

    public function getIndices(): ?array
    {
        try {
            $indices = $this->getClient()
                ->cat()
                ->indices(array(
                    'expand_wildcards'=> 'open',
                    'format' => 'JSON',
                    'pri' => true,
                    'v' => true,
                    's' => 'index'
                ))->asString()
            ;

            return json_decode($indices, true);

        } catch (ClientResponseException $clientEx) {
            $this->logger->error($clientEx->getMessage());
        } catch (ServerResponseException $resEx) {
            $this->logger->error($resEx->getMessage());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }

    public function checkIndicesExists(string $indexName): bool
    {
        try {
            return $this->getClient()->indices()->exists(array(
                'index' => $indexName
            ))->asBool();

        } catch (ClientResponseException $clientEx) {
            $this->logger->error($clientEx->getMessage());
        } catch (ServerResponseException $resEx) {
            $this->logger->error($resEx->getMessage());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return false;
    }

    protected function createNewIndices(CreateIndicesBody $createBody): array | null
    {
        try {

            $indices = $this->getClient()->indices()->create($createBody->getCreateBody());
            return json_decode($indices, true);

        } catch (ClientResponseException $clientEx) {
            $this->logger->error($clientEx->getMessage());
        } catch (ServerResponseException $resEx) {
            $this->logger->error($resEx->getMessage());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }

    protected function updateIndices(CreateIndicesBody $createBody): array| null
    {
        try {
            $indices = $this->getClient()->indices()->update($createBody->getCreateBody());

            return json_decode($indices, true);

        } catch (ClientResponseException $clientEx) {
            $this->logger->error($clientEx->getMessage());
        } catch (ServerResponseException $resEx) {
            $this->logger->error($resEx->getMessage());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return null;
    }

    protected function removeIndices(string $indexName): bool
    {
        try {
            return $this->getClient()->indices()->delete(array(
                'index' => $indexName
            ))->asBool();

        } catch (ClientResponseException $clientEx) {
            $this->logger->error($clientEx->getMessage());
        } catch (ServerResponseException $resEx) {
            $this->logger->error($resEx->getMessage());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return false;
    }
}
