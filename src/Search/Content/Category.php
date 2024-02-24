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
use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Category\CategoryEntity;

use MuckiSearchPlugin\Search\SearchClientInterface;
use MuckiSearchPlugin\Services\Content\Categories as ContentCategories;

class Category
{
    public function __construct(
        protected SearchRequest $searchRequest,
        protected ContentCategories $contentCategories
    ) {}
    public function categorySearch(
        SearchClientInterface $searchClient,
        Criteria $criteria,
        SalesChannelContext $salesChannelContext,
        array $categoryIdsOfResultsProducts
    ): ?CategoryCollection
    {
        $resultsByServerCategories = $this->searchRequest->getResultsByEntity(
            'category',
            $searchClient,
            $criteria,
            $salesChannelContext
        );

        if($resultsByServerCategories && $resultsByServerCategories['hits'] >= 1) {
            return $this->createCategoryCollection(
                $resultsByServerCategories,
                $salesChannelContext,
                $categoryIdsOfResultsProducts
            );
        }
        return null;
    }

    public function createCategoryCollection(
        array $resultByServer,
        SalesChannelContext $salesChannelContext,
        array $categoryIdsOfResultsProducts
    ): CategoryCollection
    {
        $categoryCollection = new CategoryCollection();

        if(array_key_exists('items', $resultByServer)) {

            $categories = $this->contentCategories->getCategoriesByIds(
                array_merge(
                    $this->searchRequest->getAllIdsOfSearchResult($resultByServer['items']),
                    $categoryIdsOfResultsProducts
                ),
                $salesChannelContext->getSalesChannelId()
            );

            if($categories->count() >= 1) {

                foreach ($resultByServer['items'] as $item) {

                    foreach ($item['source'] as $sourceKey => $sourceValue) {

                        if($sourceKey === 'id') {

                            $category = $categories->get($sourceValue);

                            if(array_key_exists('highlight', $item) && is_array($item['highlight'])) {

                                foreach ($item['highlight'] as $highlightKey =>  $highlightValue) {

                                    $fieldPath = explode('.',$highlightKey);
                                    if(count($fieldPath) === 1) {
                                        $category->{$fieldPath[0]} = $highlightValue[0];
                                    } else {

                                        if (in_array('translations', $fieldPath)) {

                                            $productTranslated = $category->getTranslated();
                                            $productTranslated[$fieldPath[2]] = $highlightValue[0];
                                            $category->setTranslated($productTranslated);

                                        } else {
                                            $category->{$highlightKey} = $highlightValue[0];
                                        }
                                    }
                                }
                            }
                            $categoryCollection->add($category);
                        }
                    }
                }

                /** @var CategoryEntity $category */
                foreach ($categories as $category) {

                    if(!$categoryCollection->has($category->getId())) {
                        $categoryCollection->add($category);
                    }
                }
            }
        }

        return $categoryCollection;
    }
}
