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
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

class Categories
{
    final const ENTITY_NAME = 'category';

    public function __construct(
        protected LoggerInterface $logger,
        protected EntityRepository $categoryRepository
    ){}

    public function getAllActiveCategories(string $salesChannelId): EntitySearchResult
    {
        $criteria = $this->getCriteriaAssociations($salesChannelId);

        $criteria->addSorting(
            new FieldSorting('updatedAt', FieldSorting::DESCENDING),
            new FieldSorting('createdAt', FieldSorting::DESCENDING)
        );

        return $this->categoryRepository->search($criteria, Context::createDefaultContext());
    }

    public function getCategoryByProductNumber(string $productNumber, string $salesChannelId): ?ProductEntity
    {
        $criteria = $this->getCriteriaAssociations($salesChannelId);
        $criteria->addFilter(new EqualsAnyFilter('productNumber', [$productNumber]));
        $criteria->setLimit(1);

        $product = $this->productRepository->search($criteria, Context::createDefaultContext());
        if ($product->count() >= 1) {
            return $product->first();
        } else {
            return null;
        }
    }

    public function getCategoryByCategoryId(string $categoryId, string $salesChannelId): EntitySearchResult
    {
        $criteria = $this->getCriteriaAssociations($salesChannelId);
        $criteria->addFilter(new EqualsAnyFilter('id', [$categoryId]));
        $criteria->setLimit(1);

        return $this->categoryRepository->search($criteria, Context::createDefaultContext());
    }

    protected function getCriteriaAssociations(string $salesChannelId): Criteria
    {
        return (new Criteria())
            ->addAssociation('translations')
            ->addAssociation('seoUrls')
            ->addAssociation('customFields')
            ->addAssociation('cmsPage')
            ->addFilter(new EqualsFilter('active', true))
        ;
    }
}

