<?php
/**
 * MuckiSearchPlugin plugin
 *
 *
 * @category   Muckiware
 * @package    MuckiSearch
 * @copyright  Copyright (c) 2023-2024 by Muckiware
 *
 * @author     Muckiware
 *
 */

declare(strict_types=1);

namespace MuckiSearchPlugin\Entities;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use MuckiSearchPlugin\Core\Content\IndexStructure\IndexStructureTranslation\IndexStructureTranslationEntity;

class IndexStructureInstance
{
    /**
     * Name of the indices in search server
     * @var string
     */
    protected string $indexName;

    /**
     * UUID of language
     * @var string
     */
    protected string $languageId;

    /**
     * Name of language, like english, german, etc
     * @var string
     */
    protected string $languageName;


    /**
     * Entity as just by name like product, category, order, etc
     * @var string
     */
    protected string $entity;

    /**
     * UUID of the sales channel
     * @var string
     */
    protected string $salesChannelId;

    /**
     * Language specific mappings and settings of index structure
     */
    protected IndexStructureTranslationEntity $indexStructureTranslation;

    /**
     * Total numbers of items
     * @var int
     */
    protected int $itemTotals;
    /**
     * Collection of items like products, orders, etc
     * @var array
     */
    protected array $items;

    /**
     * @return string
     */
    public function getIndexName(): string
    {
        return $this->indexName;
    }

    /**
     * @param string $indexName
     */
    public function setIndexName(string $indexName): void
    {
        $this->indexName = $indexName;
    }

    /**
     * @return string
     */
    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    /**
     * @param string $languageId
     */
    public function setLanguageId(string $languageId): void
    {
        $this->languageId = $languageId;
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @param string $entity
     */
    public function setEntity(string $entity): void
    {
        $this->entity = $entity;
    }

    /**
     * @return string
     */
    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    /**
     * @param string $salesChannelId
     */
    public function setSalesChannelId(string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    /**
     * @return IndexStructureTranslationEntity
     */
    public function getIndexStructureTranslation(): IndexStructureTranslationEntity
    {
        return $this->indexStructureTranslation;
    }

    /**
     * @param IndexStructureTranslationEntity $indexStructureTranslation
     */
    public function setIndexStructureTranslation(IndexStructureTranslationEntity $indexStructureTranslation): void
    {
        $this->indexStructureTranslation = $indexStructureTranslation;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @return int
     */
    public function getItemTotals(): int
    {
        return $this->itemTotals;
    }

    /**
     * @param int $itemTotals
     */
    public function setItemTotals(int $itemTotals): void
    {
        $this->itemTotals = $itemTotals;
    }

    /**
     * @return string
     */
    public function getLanguageName(): string
    {
        return $this->languageName;
    }

    /**
     * @param string $languageName
     */
    public function setLanguageName(string $languageName): void
    {
        $this->languageName = $languageName;
    }
}
