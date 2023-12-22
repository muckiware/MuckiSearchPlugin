<?php

namespace MuckiSearchPlugin\Services\Content;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

class Products
{

    final const ENTITY_NAME = 'product';

    public function __construct(
        protected LoggerInterface $logger,
        protected EntityRepository $productRepository
    )
    {
    }

    public function getAllActiveProduct(): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addAssociation('translations');

        return $this->productRepository->search($criteria, Context::createDefaultContext());
    }

    public function getProductByProductNumber(string $productNumber, Context $context): ?ProductEntity
    {
        if (!$context) {
            $context = Context::createDefaultContext();
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('productNumber', [$productNumber]));

        $product = $this->productRepository->search($criteria, $context);
        if ($product->count() >= 1) {
            return $product->first();
        } else {
            return null;
        }
    }
}

