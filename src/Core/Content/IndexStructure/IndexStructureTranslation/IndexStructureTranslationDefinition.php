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

use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;

use MuckiSearchPlugin\Core\Content\IndexStructure\IndexStructureDefinition;

class IndexStructureTranslationDefinition extends EntityTranslationDefinition
{
    const ENTITY_NAME = 'muwa_index_structure_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return IndexStructureTranslationEntity::class;
    }

    public function getCollectionClass(): string {
        return IndexStructureTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return IndexStructureDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new JsonField('mappings', 'mappings', [], []))->addFlags(new ApiAware()),
            new CreatedAtField(),
            new UpdatedAtField()
        ]);
    }
}
