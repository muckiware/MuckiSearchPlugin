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
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

use MuckiSearchPlugin\Search\SearchClientFactory;
use MuckiSearchPlugin\Services\Settings as PluginSettings;
use MuckiSearchPlugin\Services\IndicesSettings;

class Searching
{
    public function __construct(
        protected PluginSettings $pluginSettings,
        protected SearchClientFactory $searchClientFactory,
        protected IndicesSettings $indicesSettings
    ){}

    public function checkSearchEngineAvailable(
        Request $request,
        SalesChannelContext $salesChannelContext,
        string $entity
    ): bool
    {
        $this->indicesSettings->setTemplateVariable('entity', $entity);
        $this->indicesSettings->setTemplateVariable('salesChannelId', $salesChannelContext->getSalesChannelId());
        $this->indicesSettings->setTemplateVariable('languageId', $salesChannelContext->getLanguageId());

        $indexNameByTemplate = $this->indicesSettings->getIndexNameByTemplate();
        if($indexNameByTemplate) {

            $healthCheck = $this->searchClientFactory->createSearchClient()->getClusterHealth($indexNameByTemplate);
            if(
                $healthCheck &&
                $salesChannelContext->getContext()->getScope() === 'user' &&
                $this->pluginSettings->isEnabled() &&
                (isset($healthCheck->status) && $healthCheck->status !== 'red') &&
                $request->get('search')
            ) {
                return true;
            }
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
