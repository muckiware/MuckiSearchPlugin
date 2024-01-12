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

    /**
     * server own item id
     * @var string|null like 6e7K9IwB1n1FRIamMMtm
     */
    protected ?string $indexId;

    protected array $bodyItems;

    /**
     * @param PluginSettings $pluginSettings
     */
    public function __construct(
        protected PluginSettings $pluginSettings)
    {
        $this->indexId = null;
    }

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
    public function setBodyItems(array $bodyItems): void
    {
        $this->bodyItems = $bodyItems;
    }

    public function getIndexId(): ?string
    {
        return $this->indexId;
    }

    public function setIndexId(?string $indexId): void
    {
        $this->indexId = $indexId;
    }

    public function getIndexBody(): array
    {
        if ($this->indexId) {
            $indexBody = array(
                'id' => $this->getIndexId(),
                'index' => $this->getIndexName(),
                'body' => array(
                    'doc' => $this->getBodyItems()
                )
            );
        } else {
            $indexBody = array(
                'index' => $this->getIndexName(),
                'body' => $this->getBodyItems()
            );
        }

        return $indexBody;
    }
}
