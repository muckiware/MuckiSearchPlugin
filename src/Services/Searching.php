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

use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Symfony\Component\HttpFoundation\Request;

use MuckiSearchPlugin\Search\SearchClientFactory;
use MuckiSearchPlugin\Services\Settings as PluginSettings;

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

    public function checkProductNeedToRemove(array $payload): bool
    {
        if(array_key_exists('active', $payload) && !$payload['active']) {
            return true;
        }

        if(array_key_exists('visibilities', $payload)) {

            foreach ($payload['visibilities'] as $visibility) {

                if(
                    $visibility['visibility'] !== ProductVisibilityDefinition::VISIBILITY_ALL &&
                    $visibility['visibility'] !== ProductVisibilityDefinition::VISIBILITY_SEARCH
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
