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

use Shopware\Core\Content\Product\SalesChannel\Listing\Processor\CompositeListingProcessor;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
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

            return $searchClient->createSalesChannelProductCollection(
                $resultsByServerProducts,
                $salesChannelRepository,
                $salesChannelContext
            );
        }
        return null;
    }
}
