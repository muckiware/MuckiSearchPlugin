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
use Symfony\Component\HttpFoundation\Request;

class Searching
{
    public function __construct(
        protected PluginSettings $pluginSettings,
        protected SearchClientFactory $searchClientFactory
    ){}

    public function checkSearchEngineAvailable(Request $request, string $scope): bool
    {
        if(
            $scope === 'user' &&
            $this->pluginSettings->isEnabled() &&
            $this->searchClientFactory->createSearchClient()->getServerInfoAsObject() &&
            $request->get('search')
        ) {
            return true;
        }
        return false;
    }
}
