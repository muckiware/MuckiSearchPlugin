<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Core\Content\IndexStructure;

use MuckiSearchPlugin\Core\Content\Banner\IndexStructureEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class IndexStructureCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return IndexStructureEntity::class;
    }
}
