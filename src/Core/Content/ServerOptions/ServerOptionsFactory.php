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

namespace MuckiSearchPlugin\Core\Content\ServerOptions;

use Psr\Log\LoggerInterface;
use Shopware\Core\System\SystemConfig\Exception\ConfigurationNotFoundException;

use MuckiSearchPlugin\Core\Content\ServerOptions\MappingOptionsInterface;
use MuckiSearchPlugin\Core\Content\ServerOptions\Elasticsearch\MappingOptions as ElasticsearchMappingOptions;
use MuckiSearchPlugin\Core\Content\ServerOptions\Opensearch\MappingOptions as OpensearchMappingOptions;
use MuckiSearchPlugin\Services\Settings as PluginSettings;

class ServerOptionsFactory
{
    public function __construct(
        protected PluginSettings $settings,
        protected LoggerInterface $logger
    )
    {}

    /**
     * @throws ConfigurationNotFoundException
     */
    public function createServerOptions(?string $serverType= null): MappingOptionsInterface
    {
        if(!$serverType) {
            $serverType = $this->settings->getServerType();
        }
        return match ($serverType) {
            'opensearch' => new OpensearchMappingOptions(),
            'elasticsearch' => new ElasticsearchMappingOptions(),
            default => throw new ConfigurationNotFoundException('Missing server type'),
        };
    }
}
