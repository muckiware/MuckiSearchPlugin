<?php declare(strict_types=1);
/**
 * MuckiSearchPlugin plugin
 *
 *
 * @category   Muckiware
 * @package    MuckiSearch
 * @copyright  Copyright (c) 2023-2024 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */

namespace MuckiSearchPlugin\Search\Content;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

use MuckiSearchPlugin\Search\SearchClientInterface;
use MuckiSearchPlugin\Services\Content\IndexStructure;
use MuckiSearchPlugin\Services\IndicesSettings;
use MuckiSearchPlugin\Services\Settings as PluginSettings;

class SearchRequest
{
    public function __construct(
        protected IndexStructure $indexStructure,
        protected IndicesSettings $indicesSettings,
        protected PluginSettings $pluginSettings
    ) {}
    public function getResultsByEntity(
        string $entity,
        SearchClientInterface $searchClient,
        Criteria $criteria,
        SalesChannelContext $salesChannelContext
    ): ?array
    {
        $currentIndexStructure = $this->indexStructure->getCurrentIndexStructure(
            $entity,
            $salesChannelContext->getLanguageId(),
            $salesChannelContext->getSalesChannelId(),
            $salesChannelContext->getContext()
        );

        $this->indicesSettings->setTemplateVariable('entity', $entity);
        $this->indicesSettings->setTemplateVariable('salesChannelId', $salesChannelContext->getSalesChannelId());
        $this->indicesSettings->setTemplateVariable('languageId', $salesChannelContext->getLanguageId());

        $searchQueryRequestBody = array(
            'query' => array (
                'bool' => array(
                    'should' => $searchClient->createQueryObject(
                        $criteria,
                        $currentIndexStructure->get('mappings')
                    )
                )
            )
        );

        $highlightObject = $searchClient->createHighlightObject(
            $this->pluginSettings,
            $currentIndexStructure->get('mappings')
        );
        if(!empty($highlightObject)) {
            $searchQueryRequestBody['highlight'] = $highlightObject;
        }

        return $searchClient->searching(array(
            'index' => $this->indicesSettings->getIndexNameByTemplate(),
            'body' => $searchQueryRequestBody
        ));
    }

    public function getAllIdsOfSearchResult(array $resultByServer): array
    {
        $ids = array();
        foreach ($resultByServer as $item) {

            foreach ($item['source'] as $sourceKey => $sourceValue) {

                if($sourceKey === 'id') {
                    $ids[] = $sourceValue;
                }
            }
        }

        return $ids;
    }
}
