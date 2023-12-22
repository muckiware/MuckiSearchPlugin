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

namespace MuckiSearchPlugin\Search;

use Exception;
use Psr\Log\LoggerInterface;
use Shopware\Core\System\SystemConfig\Exception\ConfigurationNotFoundException;

use MuckiSearchPlugin\Search\Elasticsearch\Client as ElasticsearchClient;
use MuckiSearchPlugin\Search\Opensearch\Client as OpensearchClient;
use MuckiSearchPlugin\Services\Settings as PluginSettings;

class SearchClientFactory
{
    public function __construct(
        protected PluginSettings $settings,
        protected LoggerInterface $logger
    )
    {}

    /**
     * @throws Exception
     */
    public function createSearchClient(?string $serverType= null): SearchClientInterface
    {
        if(!$serverType) {
            $serverType = $this->settings->getServerType();
        }
        return match ($serverType) {
            'opensearch' => new OpensearchClient($this->settings, $this->logger),
            'elasticsearch' => new ElasticsearchClient($this->settings, $this->logger),
            default => throw new ConfigurationNotFoundException('Missing server type'),
        };
    }
}
