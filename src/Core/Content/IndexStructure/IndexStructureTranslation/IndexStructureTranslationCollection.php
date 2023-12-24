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

namespace MuckiSearchPlugin\Core\Content\IndexStructure\IndexStructureTranslation;

use MuckiSearchPlugin\Core\Content\IndexStructure\IndexStructureTranslation\IndexStructureTranslationEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class IndexStructureTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return IndexStructureTranslationEntity::class;
    }
}
