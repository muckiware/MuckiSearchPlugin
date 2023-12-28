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
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteException;

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
    public function saveMappingsByLanguageId(
        array $mapping,
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
                        'mappings' => $mapping
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
}
