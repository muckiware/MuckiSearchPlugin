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

use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Language\LanguageDefinition;

use MuckiSearchPlugin\Core\Content\SearchRequestLogs\SearchRequestLogsDefinition;

class SearchRequestLogsTranslationDefinition extends EntityTranslationDefinition
{
    const ENTITY_NAME = 'muwa_search_request_logs_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return SearchRequestLogsTranslationEntity::class;
    }

    public function getCollectionClass(): string {
        return SearchRequestLogsTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return SearchRequestLogsDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('language_id', 'languageId', LanguageDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new StringField('search_term', 'searchTerm'))->addFlags(new ApiAware()),
            (new IntField('hits', 'hits')),
            new CreatedAtField(),
            new UpdatedAtField()
        ]);
    }
}
