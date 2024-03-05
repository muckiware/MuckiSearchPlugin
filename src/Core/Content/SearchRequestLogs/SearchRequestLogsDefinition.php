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

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
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
use Shopware\Core\Framework\DataAbstractionLayer\Field\VersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

use MuckiSearchPlugin\Core\Content\SearchRequestLogs\SearchRequestLogsTranslation\SearchRequestLogsTranslationDefinition;

class SearchRequestLogsDefinition extends EntityDefinition
{
    const ENTITY_NAME = 'muwa_search_request_logs';

    public function getEntityName(): string {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string {
        return SearchRequestLogsEntity::class;
    }

    public function getCollectionClass(): string {
        return SearchRequestLogsCollection::class;
    }

    protected function defineFields(): FieldCollection {
        return new FieldCollection([
            (new idField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new VersionField())->addFlags(new ApiAware()),
            (new FkField('sales_channel_id', 'salesChannelId', SalesChannelDefinition::class))->addFlags(new Required()),
            (new DateTimeField('request_date_time', 'requestDateTime'))->addFlags(new ApiAware(), new Inherited()),
            (new StringField('user_agent', 'userAgent'))->addFlags(new ApiAware()),
            (new StringField('device', 'device'))->addFlags(new ApiAware()),
            (new StringField('platform', 'platform'))->addFlags(new ApiAware()),
            (new StringField('platform_version', 'platformVersion'))->addFlags(new ApiAware()),
            (new StringField('browser', 'browser'))->addFlags(new ApiAware()),
            (new StringField('browser_version', 'browserVersion'))->addFlags(new ApiAware()),
            (new BoolField('is_desktop', 'isDesktop'))->addFlags(new ApiAware(), new Inherited()),
            (new BoolField('is_mobile', 'isMobile'))->addFlags(new ApiAware(), new Inherited()),
            (new TranslatedField('searchTerm')),
            (new TranslatedField('hits')),
            (new TranslationsAssociationField(SearchRequestLogsTranslationDefinition::class, 'muwa_index_structure_id'))->addFlags(new Required()),
            new ManyToOneAssociationField('salesChannel', 'sales_channel_id', SalesChannelDefinition::class, 'id', false),
            new CreatedAtField(),
            new UpdatedAtField()
        ]);
    }
}
