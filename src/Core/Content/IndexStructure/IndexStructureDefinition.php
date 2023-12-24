<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Core\Content\IndexStructure;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

use MuckiSearchPlugin\Core\Content\IndexStructure\IndexStructureTranslation\IndexStructureTranslationDefinition;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelCountry\SalesChannelCountryDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

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
            (new FkField('sales_channel_id', 'salesChannelId', SalesChannelDefinition::class))->addFlags(new Required()),
            (new BoolField('active', 'active'))->addFlags(new ApiAware(), new Inherited()),
            (new StringField('name', 'name'))->addFlags(
                new ApiAware(),
                new Inherited(),
                new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING, false)
            ),
            (new StringField('entity', 'entity'))->addFlags(new ApiAware()),
            (new TranslationsAssociationField(IndexStructureTranslationDefinition::class, 'muwa_index_structure_id'))->addFlags(new Required()),
            //(new TranslationsAssociationField(PseudoProductTranslationDefinition::class, 'lightson_pseudo_product_id'))->addFlags(new Required()),

            new ManyToOneAssociationField('salesChannel', 'sales_channel_id', SalesChannelDefinition::class),
            (new TranslatedField('mappings')),
            new CreatedAtField(),
            new UpdatedAtField()
        ]);
    }
}
