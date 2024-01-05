<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Entities;

use MuckiSearchPlugin\Core\Defaults;
use MuckiSearchPlugin\Services\Settings as PluginSettings;
use Shopware\Core\Framework\Uuid\Uuid;

class CreateIndexBody
{
    /**
     * UUID for a search mapping object
     * @var string
     */
    protected string $indexName;

    protected array $bodyItems;

    /**
     * @param PluginSettings $pluginSettings
     */
    public function __construct(
        protected PluginSettings $pluginSettings)
    {}

    /**
     * @return string
     */
    public function getIndexName(): string
    {
        return $this->indexName;
    }

    /**
     * @param string $indexName
     */
    public function setIndexName(string $indexName): void
    {
        $this->indexName = $indexName;
    }

    /**
     * @return array
     */
    public function getBodyItems(): array
    {
        return $this->bodyItems;
    }

    /**
     * @param array $bodyItems
     */
    public function setBodyItem(string $key, string $value): void
    {
        $this->bodyItems[$key] = $value;
    }

    public function getIndexBody(): array
    {
        return array(
            'index' => $this->getIndexName(),
            'body' => $this->getBodyItems()
        );
    }
}
