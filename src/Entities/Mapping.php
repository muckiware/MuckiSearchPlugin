<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Entities;

use MuckiSearchPlugin\Services\Settings as PluginSettings;
use Shopware\Core\Framework\Uuid\Uuid;

class Mapping
{
    /**
     * UUID for a search mapping object
     * @var string
     */
    protected string $id;

    protected bool $isDefault;

    protected string $dataType;

    protected string $key;

    protected ?string $mappedKey;

    protected int $position;

    /**
     * @param PluginSettings $pluginSettings
     */
    public function __construct()
    {
        $this->id = Uuid::randomHex();
        $this->isDefault = true;
        $this->mappedKey = null;
        $this->position = 0;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * @param bool $isDefault
     */
    public function setIsDefault(bool $isDefault): void
    {
        $this->isDefault = $isDefault;
    }

    /**
     * @return string
     */
    public function getDataType(): string
    {
        return $this->dataType;
    }

    /**
     * @param string $dataType
     */
    public function setDataType(string $dataType): void
    {
        $this->dataType = $dataType;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return string | null
     */
    public function getMappedKey(): ?string
    {
        return $this->mappedKey;
    }

    /**
     * @param string $mappedKey
     */
    public function setMappedKey(string $mappedKey): void
    {
        $this->mappedKey = $mappedKey;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getMapping(): array
    {
        return array(
            'id' => $this->getId(),
            'isDefault' => $this->isDefault(),
            'dataType' => $this->getDataType(),
            'key' => $this->getKey(),
            'mappedKey' => $this->getMappedKey(),
            'position' => $this->getPosition()
        );
    }
}
