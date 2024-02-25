<?php declare(strict_types=1);
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
namespace MuckiSearchPlugin\Core\Content\SearchRequestLogs\SearchRequestLogsTranslation;

use MuckiSearchPlugin\Core\Content\SearchRequestLogs\SearchRequestLogsTranslation\SearchRequestLogsTranslationEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class SearchRequestLogsTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return SearchRequestLogsTranslationEntity::class;
    }
}
