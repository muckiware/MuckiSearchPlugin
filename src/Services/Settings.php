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

namespace MuckiSearchPlugin\Services;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class Settings
{
    const CONFIG_PATH_ACTIVE = 'MuckiSearchPlugin.config.active';
    const CONFIG_PATH_SERVER_TYPE = 'MuckiSearchPlugin.config.serverType';

    const CONFIG_PATH_SERVER_HOST = 'MuckiSearchPlugin.config.serverHost';
    const CONFIG_PATH_SERVER_PORT = 'MuckiSearchPlugin.config.serverPort';

    public function __construct(
        protected SystemConfigService $config
    ){}
    
    public function isEnabled(): bool
    {
        return $this->config->getBool($this::CONFIG_PATH_ACTIVE);
    }

    public function getServerHost(): string
    {
        return $this->config->getString($this::CONFIG_PATH_SERVER_HOST);
    }

    public function getServerPort(): int
    {
        return $this->config->getInt($this::CONFIG_PATH_SERVER_PORT);
    }

    public function getServerConnectionString(): string
    {
       return $this->getServerHost().':'.$this->getServerPort();
    }

    public function getServerType(): string
    {
        return $this->config->getString($this::CONFIG_PATH_SERVER_TYPE);
    }
}
