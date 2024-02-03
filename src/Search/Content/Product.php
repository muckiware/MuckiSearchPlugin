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

use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\SalesChannel\Listing\Processor\CompositeListingProcessor;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

use MuckiSearchPlugin\Search\SearchClientInterface;

class Product
{
    public function __construct(
        protected SearchRequest $searchRequest
    ) {}
    public function productSearch(
        SearchClientInterface $searchClient,
        Criteria $criteria,
        SalesChannelRepository $salesChannelRepository,
        SalesChannelContext $salesChannelContext
    ): ?SalesChannelProductCollection
    {
        $resultsByServerProducts = $this->searchRequest->getResultsByEntity(
            'product',
            $searchClient,
            $criteria,
            $salesChannelContext
        );

        if($resultsByServerProducts && $resultsByServerProducts['hits'] >= 1) {

            return $this->createSalesChannelProductCollection(
                $resultsByServerProducts,
                $salesChannelRepository,
                $salesChannelContext
            );
        }
        return null;
    }

    public function createSalesChannelProductCollection(
        array $resultByServer,
        SalesChannelRepository $salesChannelRepository,
        SalesChannelContext $salesChannelContext
    ): SalesChannelProductCollection
    {
        $alesChannelProductCollection = new SalesChannelProductCollection();

        if(array_key_exists('items', $resultByServer)) {

            $salesChannelProducts = $this->getAllSalesChannelProductsByIds(
                $salesChannelRepository,
                $this->searchRequest->getAllIdsOfSearchResult($resultByServer['items']),
                $salesChannelContext
            );

            if($salesChannelProducts->count() >= 1) {

                foreach ($resultByServer['items'] as $item) {

                    foreach ($item['source'] as $sourceKey => $sourceValue) {

                        if($sourceKey === 'id') {

                            $salesChannelProduct = $salesChannelProducts->get($sourceValue);

                            if(array_key_exists('highlight', $item)) {

                                foreach ($item['highlight'] as $highlightKey =>  $highlightValue) {

                                    $fieldPath = explode('.',$highlightKey);
                                    if(count($fieldPath) === 1) {
                                        $salesChannelProduct->{$fieldPath[0]} = $highlightValue[0];
                                    } else {

                                        if (in_array('translations', $fieldPath)) {

                                            $productTranslated = $salesChannelProduct->getTranslated();
                                            $productTranslated[$fieldPath[2]] = $highlightValue[0];
                                            $salesChannelProduct->setTranslated($productTranslated);

                                        } else {
                                            $salesChannelProduct->{$highlightKey} = $highlightValue[0];
                                        }
                                    }
                                }
                            }
                            $alesChannelProductCollection->add($salesChannelProduct);
                        }
                    }
                }
            }
        }

        return $alesChannelProductCollection;
    }

    protected function getSalesChannelProductById(
        SalesChannelRepository $salesChannelRepository,
        string $productId,
        SalesChannelContext $salesChannelContext
    ): EntitySearchResult
    {
        $criteria = $this->getCriteriaAssociations($salesChannelContext->getSalesChannelId());
        $criteria->addFilter(new EqualsFilter('id', $productId));
        $criteria->setLimit(1);

        return $salesChannelRepository->search($criteria, $salesChannelContext);
    }

    protected function getAllSalesChannelProductsByIds(
        SalesChannelRepository $salesChannelRepository,
        array $productIds,
        SalesChannelContext $salesChannelContext
    ): EntitySearchResult
    {
        $criteria = $this->getCriteriaAssociations($salesChannelContext->getSalesChannelId());
        $criteria->addFilter(new EqualsAnyFilter('id', $productIds));

        return $salesChannelRepository->search($criteria, $salesChannelContext);
    }

    protected function getCriteriaAssociations(string $salesChannelId): Criteria
    {
        return (new Criteria())
            ->addAssociation('translations')
            ->addAssociation('manufacturer.media')
            ->addAssociation('options.group')
            ->addAssociation('properties.group')
            ->addAssociation('mainCategories.category')
            ->addAssociation('media')
            ->addAssociation('visibilities')
            ->addAssociation('seoUrls')
            ->addAssociation('tags')
            ->addAssociation('categories')
            ->addAssociation('cover')
            ->addFilter(new EqualsFilter('active', true))
            ->addFilter(new EqualsFilter('seoUrls.isCanonical', true))
            ->addFilter(new EqualsFilter('visibilities.salesChannelId', $salesChannelId))
            ->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
                new EqualsFilter('visibilities.visibility', ProductVisibilityDefinition::VISIBILITY_SEARCH),
                new EqualsFilter('visibilities.visibility', ProductVisibilityDefinition::VISIBILITY_ALL)
            ]))
            ;
    }
}
