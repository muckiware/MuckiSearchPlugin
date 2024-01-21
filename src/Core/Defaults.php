<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Core;

/**
 * Plugin wide default values
 */
final class Defaults
{
    public const DEFAULT_SERVER_HOST = 'localhost';

    public const DEFAULT_SERVER_PORT = 9200;

    public const DEFAULT_INDEX_NAME_PATTERN = '{{salesChannelId}}-{{entity}}-{{languageId}}';

    public const DEFAULT_PRODUCT_MAPPINGS = 'id:keyword,productNumber:keyword,translations.DEFAULT.name:text,translations.DEFAULT.description:text,cover.media.url:text';

    public const DEFAULT_NUMBER_SHARDS = 2;

    public const DEFAULT_NUMBER_REPLICAS = 1;

    public const INDICES_SETTINGS_NUMBER_SHARDS = 'number_of_shards';
    public const INDICES_SETTINGS_NUMBER_REPLICAS = 'number_of_replicas';

    public const SEARCH_REQUEST_SETTINGS_PRE_TAGS = '<b>';
    public const SEARCH_REQUEST_SETTINGS_POST_TAGS = '</b>';
}
