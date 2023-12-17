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

use Symfony\Component\HttpKernel\KernelInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class Settings
{
    const CONFIG_PATH_ACTIVE = 'MuckiSearchPlugin.config.active';
    const CONFIG_PATH_SERVER_HOST = 'MuckiSearchPlugin.config.serverHost';
    const CONFIG_PATH_SERVER_Port = 'MuckiSearchPlugin.config.serverPort';

    private SystemConfigService $config;

    private KernelInterface $kernel;

    public function __construct(
        SystemConfigService $config,
        KernelInterface $kernel
    )
    {
        $this->config = $config;
        $this->kernel = $kernel;
    }
    
    public function isEnabled()
    {
        return $this->config->get($this::CONFIG_PATH_ACTIVE);
    }

    public function getServerHost(): string
    {

    }

    public function getServerPort(): int
    {

    }

    public function getServerConnectionString(): string
    {
       return'';
    }
}

