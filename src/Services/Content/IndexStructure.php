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
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;

use MuckiSearchPlugin\Entities\SearchMapping;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteException;
use MuckiSearchPlugin\Core\Content\IndexStructure\IndexStructureEntity;

class IndexStructure
{
    public function __construct(
        protected LoggerInterface $logger,
        protected EntityRepository $indexStructureRepository,
        protected EntityRepository $indexStructureTranslationRepository
    )
    {
    }

    /**
     * @throws \Exception
     */
    public function saveMappingsSettingsByLanguageId(
        array $mapping,
        array $settings,
        string $indexStructureId,
        string $languageId,
        Context $context
    ): ?EntityWrittenContainerEvent
    {
        try {
            return $this->indexStructureTranslationRepository->update(
                array(
                    array(
                        'muwaIndexStructureId' => $indexStructureId,
                        'languageId' => $languageId,
                        'updated_at' => new \DateTime(),
                        'mappings' => $mapping,
                        'settings' => $settings
                    ),
                ),
                $context
            );
        } catch (WriteException $exception) {
            $this->logger->error('Update mapping not possible');
            $this->logger->error($exception->getMessage());
        }

        return null;
    }

    public function getAllActiveIndexStructure(): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING));
        $criteria->addAssociation('translations');
        $criteria->addAssociation('translations.language');
        $criteria->addAssociation('translations.language.translationCode');

        return $this->indexStructureRepository->search($criteria, Context::createDefaultContext());
    }

    public function getIndexStructureById(
        string $indexStructureId,
        string $languageId,
        Context $context
    ): ?IndexStructureEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('id', [$indexStructureId]));
        $criteria->addFilter(new EqualsAnyFilter('translations.languageId', [$languageId]));
        $criteria->addAssociation('translations');
        $criteria->addAssociation('translations.mappings');

        $indexStructureResult = $this->indexStructureRepository->search($criteria, $context);
        if ($indexStructureResult->count() >= 1) {
            return $indexStructureResult->first();
        } else {
            return null;
        }
    }

    public function getCurrentIndexStructure(
        string $entity,
        string $languageId,
        string $salesChannelId,
        Context $context
    ): ?IndexStructureEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('entity', $entity));
        $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));
        $criteria->addFilter(new EqualsFilter('translations.languageId', $languageId));
        $criteria->addAssociation('translations');
        $criteria->addAssociation('translations.mappings');

        $indexStructureResult = $this->indexStructureRepository->search($criteria, $context);
        if ($indexStructureResult->count() >= 1) {
            return $indexStructureResult->first();
        } else {
            return null;
        }
    }

    public function removeIndexStructureById(string $indexStructureId, Context $context): ?EntityWrittenContainerEvent
    {
        try {
            return $this->indexStructureRepository->delete(
                array(array('id' => $indexStructureId)), $context
            );
        } catch (WriteException $exception) {
            $this->logger->error('Update mapping not possible');
            $this->logger->error($exception->getMessage());
        }

        return null;
    }
}

