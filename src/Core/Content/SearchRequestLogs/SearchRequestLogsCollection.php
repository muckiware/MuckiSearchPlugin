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
namespace MuckiSearchPlugin\Core\Content\SearchRequestLogs;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

use MuckiSearchPlugin\Core\Content\SearchRequestLogs\SearchRequestLogsEntity;

class SearchRequestLogsCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return SearchRequestLogsEntity::class;
    }
}
