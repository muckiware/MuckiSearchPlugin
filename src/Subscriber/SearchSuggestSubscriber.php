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

namespace MuckiSearchPlugin\Subscriber;

use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Content\Product\SalesChannel\Listing\Processor\CompositeListingProcessor;
use Shopware\Core\Content\Product\SearchKeyword\ProductSearchBuilderInterface;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\Content\Product\Events\ProductSuggestResultEvent;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use MuckiSearchPlugin\Services\Settings as PluginSettings;
use MuckiSearchPlugin\Search\SearchClientFactory;
use MuckiSearchPlugin\Search\Content\Category as ContentCategory;

class SearchSuggestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected PluginSettings $pluginSettings,
        protected RequestStack $requestStack,
        protected CompositeListingProcessor $processor,
        protected SearchClientFactory $searchClientFactory,
        protected ProductSearchBuilderInterface $searchBuilder,
        protected ContentCategory $contentCategory
    )
    {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_SUGGEST_RESULT => 'onProductSuggestResult'
        ];
    }

    public function onProductSuggestResult(ProductSuggestResultEvent $event): void
    {
        $searchClient = $this->searchClientFactory->createSearchClient();
        $request = $this->requestStack->getCurrentRequest();

        if($request) {

            $this->processor->prepare($request, $event->getResult()->getCriteria(), $event->getSalesChannelContext());
            $this->searchBuilder->build($request, $event->getResult()->getCriteria(), $event->getSalesChannelContext());

            $categoryIdsOfResultsProducts = $this->getCategoryIdsOfResultsProducts(
                $event->getResult()->getElements()
            );

            $categorySearchCollection = $this->contentCategory->categorySearch(
                $searchClient,
                $event->getResult()->getCriteria(),
                $event->getSalesChannelContext(),
                $categoryIdsOfResultsProducts
            );

            if($categorySearchCollection) {

                $event->getResult()->addExtensions(
                    array('searchResultCategories' => $categorySearchCollection)
                );
            }
        }
    }

    protected function getCategoryIdsOfResultsProducts(array $products): array
    {
        $categoryIdsOfResultsProducts = array();
        /** @var SalesChannelProductEntity $product */
        foreach ($products as $product) {

            $productCategories = $product->getCategories();
            if($productCategories) {

                foreach ($productCategories as $categoryKey => $category) {

                    if(!in_array($categoryKey, $categoryIdsOfResultsProducts)) {
                        $categoryIdsOfResultsProducts[] = $categoryKey;
                    }
                }
            }
        }
        return $categoryIdsOfResultsProducts;
    }
}
