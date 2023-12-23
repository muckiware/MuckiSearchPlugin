<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Core\Content\Banner;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
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
            (new StringField('type', 'type'))->addFlags(new Required()),
            new IntField('height', 'height'),
            (new StringField('label', 'label'))->addFlags(new Required()),
            new StringField('flashing_speed', 'flashingSpeed'),
            new StringField('discount_code', 'discountCode'),
            new BoolField('sticky', 'sticky'),
            new BoolField('target', 'target'),
            new BoolField('modal', 'modal'),
            new IntField('start_date', 'startDate'),
            new IntField('expiry_date', 'expiryDate'),
            new BoolField('button_check', 'buttonCheck'),
            new BoolField('button_url_check', 'buttonUrlCheck'),
            new StringField('text_color', 'textColor'),
            new StringField('background_color', 'backgroundColor'),
            new StringField('button_text_color', 'buttonTextColor'),
            new StringField('button_background_color', 'buttonBackgroundColor'),
            new TranslatedField('modalContent'),
            new TranslatedField('active'),
            new TranslatedField('content'),
            new TranslatedField('url'),
            new TranslatedField('buttonText'),
            (new TranslationsAssociationField(BannerTranslationDefinition::class, 'cogi_banner_id'))->addFlags(new Required()),

        ]);
    }
}
