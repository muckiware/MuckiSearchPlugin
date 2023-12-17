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

    public function __construct(
        SystemConfigService $config,
        KernelInterface $kernel
    )
    {
        $this->_config = $config;
        $this->kernel = $kernel;
    }
    
    public function isEnabled()
    {
        return $this->_config->get($this::CONFIG_PATH_ACTIVE);
    }
}

