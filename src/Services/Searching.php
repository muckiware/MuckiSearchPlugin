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

use MuckiSearchPlugin\Search\SearchClientFactory;
use MuckiSearchPlugin\Services\Settings as PluginSettings;

class Searching
{
    public function __construct(
        protected PluginSettings $pluginSettings,
        protected SearchClientFactory $searchClientFactory
    ){}

    public function checkSearchEngineAvailable(string $scope): bool
    {
        if(
            $scope === 'user' &&
            $this->pluginSettings->isEnabled() &&
            $this->searchClientFactory->createSearchClient()->getServerInfoAsObject()
        ) {
            return true;
        }
        return false;
    }
}
