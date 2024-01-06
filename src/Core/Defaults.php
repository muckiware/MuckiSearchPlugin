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

    public const DEFAULT_PRODUCT_MAPPINGS = 'id:keywords,productNumber:keywords,translations.DEFAULT.name:text,translations.DEFAULT.description:text,cover.media.url:text';

    public const DEFAULT_NUMBER_SHARDS = 2;

    public const DEFAULT_NUMBER_REPLICAS = 1;

    public const INDICES_SETTINGS_NUMBER_SHARDS = 'numbers_of_shards';
    public const INDICES_SETTINGS_NUMBER_REPLICAS = 'numbers_of_replicas';
}
