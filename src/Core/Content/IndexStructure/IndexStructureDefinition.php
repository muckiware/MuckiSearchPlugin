<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Core\Content\IndexStructure;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class IndexStructureDefinition extends EntityDefinition
{
    const ENTITY_NAME = 'muwa_index_structure';

    public function getEntityName(): string {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string {
        return IndexStructureEntity::class;
    }

    public function getCollectionClass(): string {
        return IndexStructureCollection::class;
    }

    protected function defineFields(): FieldCollection {
        return new FieldCollection([
            (new idField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new BoolField('active', 'active'))->addFlags(new ApiAware(), new Inherited()),
            (new StringField('name', 'name'))->addFlags(
                new ApiAware(),
                new Inherited(),
                new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING, false)
            ),
            new JsonField('mappings', 'mappings'),
            new CreatedAtField(),
            new UpdatedAtField()
        ]);
    }
}
