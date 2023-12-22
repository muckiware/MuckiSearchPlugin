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
use Psr\Log\LoggerInterface;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\ClientInterface;
use Elastic\Elasticsearch\Exception\AuthenticationException;

use MuckiSearchPlugin\Search\SearchClientInterface;
use MuckiSearchPlugin\Services\Settings as PluginSettings;

class Client implements SearchClientInterface
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

            return $this->getClient()
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

    public function deleting(array $params): bool
    {
        $response['acknowledge'] = 0;
        try {
            $response = $this->getClient()->delete($params);
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
}
