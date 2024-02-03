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
        SalesChannelContext $salesChannelContext
    ): ?CategoryCollection
    {
        $resultsByServerCategories = $this->searchRequest->getResultsByEntity(
            'category',
            $searchClient,
            $criteria,
            $salesChannelContext
        );

        if($resultsByServerCategories && $resultsByServerCategories['hits'] >= 1) {
            return $this->createCategoryCollection($resultsByServerCategories, $salesChannelContext);
        }
        return null;
    }

    public function createCategoryCollection(
        array $resultByServer,
        SalesChannelContext $salesChannelContext
    ): CategoryCollection
    {
        $categoryCollection = new CategoryCollection();

        if(array_key_exists('items', $resultByServer)) {

            $categories = $this->contentCategories->getCategoriesByIds(
                $this->searchRequest->getAllIdsOfSearchResult($resultByServer['items']),
                $salesChannelContext->getSalesChannelId()
            );

            if($categories->count() >= 1) {

                foreach ($resultByServer['items'] as $item) {

                    foreach ($item['source'] as $sourceKey => $sourceValue) {

                        if($sourceKey === 'id') {

                            $category = $categories->get($sourceValue);

                            if(array_key_exists('highlight', $item)) {

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
            }
        }

        return $categoryCollection;
    }
}