<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Entities;

use MuckiSearchPlugin\Core\Defaults;
use MuckiSearchPlugin\Services\Settings as PluginSettings;
use Shopware\Core\Framework\Uuid\Uuid;

class CreateIndicesBody
{
    /**
     * UUID for a search mapping object
     * @var string
     */
    protected string $indexName;

    protected string $indexId;

    protected int $numberOfShards;

    protected int $numberOfReplicas;

    protected array $mappings;

    /**
     * @param PluginSettings $pluginSettings
     */
    public function __construct(
        protected PluginSettings $pluginSettings)
    {}


    /**
     * @return int
     */
    public function getNumberOfShards(): int
    {
        return $this->numberOfShards;
    }

    /**
     * @param int $numberOfShards
     */
    public function setNumberOfShards(int $numberOfShards): void
    {
        $this->numberOfShards = $numberOfShards;
    }

    /**
     * @return int
     */
    public function getNumberOfReplicas(): int
    {
        return $this->numberOfReplicas;
    }

    /**
     * @param int $numberOfReplicas
     */
    public function setNumberOfReplicas(int $numberOfReplicas): void
    {
        $this->numberOfReplicas = $numberOfReplicas;
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
     * @return string
     */
    public function getIndexId(): string
    {
        return $this->indexId;
    }

    /**
     * @param string $indexId
     */
    public function setIndexId(string $indexId): void
    {
        $this->indexId = $indexId;
    }

    /**
     * @return array
     */
    public function getMappings(): array
    {
        return $this->mappings;
    }

    /**
     * @param array $mappings
     */
    public function setMappings(array $mappings): void
    {
        $this->mappings = $mappings;
    }

    public function getCreateBody(): array
    {
        return array(
            'index' => $this->getIndexName(),
            'body' => array(
                'settings' => array(
                    Defaults::INDICES_SETTINGS_NUMBER_SHARDS => $this->getNumberOfShards(),
                    Defaults::INDICES_SETTINGS_NUMBER_REPLICAS => $this->getNumberOfReplicas()
                ),
                'mappings' => $this->getMappings()
            )
        );
    }
}
