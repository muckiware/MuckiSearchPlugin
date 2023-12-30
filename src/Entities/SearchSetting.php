<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Entities;

class SearchSetting
{
    /**
     * UUID for a search mapping object
     * @var string
     */
    protected string $id;

    protected string $key;

    protected string $mappedKey;

    protected int $position;

    protected bool $isDefault;

    protected string $settingKey;

    protected string | int | bool $settingValue;

    public function __construct()
    {
        $this->isDefault = false;
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
     * @return string
     */
    public function getMappedKey(): string
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
    public function getSettingKey(): string
    {
        return $this->settingKey;
    }

    /**
     * @param string $settingKey
     */
    public function setSettingKey(string $settingKey): void
    {
        $this->settingKey = $settingKey;
    }

    /**
     * @return bool|int|string
     */
    public function getSettingValue(): bool|int|string
    {
        return $this->settingValue;
    }

    /**
     * @param bool|int|string $settingValue
     */
    public function setSettingValue(bool|int|string $settingValue): void
    {
        $this->settingValue = $settingValue;
    }

    public function getSettingObject(): array
    {
        return array(
            'id' => $this->getId(),
            'key' => $this->getKey(),
            'mappedKey' => $this->getMappedKey(),
            'position' => $this->getPosition(),
            'isDefault' => $this->isDefault(),
            $this->getSettingKey() => $this->getSettingValue()
        );
    }
}
