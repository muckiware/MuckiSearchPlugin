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

use MuckiSearchPlugin\Entities\SearchMapping;

class IndexStructure
{
    public function __construct(
        protected LoggerInterface $logger,
        protected EntityRepository $indexStructureRepository,
        protected EntityRepository $indexStructureTranslationRepository
    )
    {
    }

    public function saveMappingsByLanguageId(array $mapping, string $indexStructureId, string $languageId): void
    {
        $updateResult = $this->indexStructureTranslationRepository->update(
            array(
                'id' => $indexStructureId,
                'languageId' => $languageId,
                'updated_at' => new \DateTime(),
                'translated' => array(
                    'mappings' => $mapping
                )
            )
        );
    }
}

