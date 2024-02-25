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
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Content\Category\CategoryDefinition;

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

    public function getCategoryByCategoryId(string $categoryId, string $salesChannelId): EntitySearchResult
    {
        $criteria = $this->getCriteriaAssociations($salesChannelId);
        $criteria->addFilter(new EqualsAnyFilter('id', [$categoryId]));
        $criteria->setLimit(1);

        return $this->categoryRepository->search($criteria, Context::createDefaultContext());
    }

    public function getCategoriesByIds(array $categoryIds, string $salesChannelId): EntitySearchResult
    {
        $criteria = $this->getCriteriaAssociations($salesChannelId);
        $criteria->addFilter(new EqualsAnyFilter('id', $categoryIds));

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
            ->addFilter(new EqualsFilter('type', CategoryDefinition::TYPE_PAGE))
        ;
    }

    public function getItems(?string $itemId, string $salesChannelId): array
    {
        if ($itemId) {
            return $this->getCategoryByCategoryId($itemId, $salesChannelId)->getElements();
        } else {
            return $this->getAllActiveCategories($salesChannelId)->getElements();
        }
    }
}

