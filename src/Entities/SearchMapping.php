<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Entities;

class SearchMapping
{
    /**
     * UUID for a search mapping object
     * @var string
     */
    protected string $id;

    protected string $key;

    protected string $mappedKey;

    protected int $position;

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

    public function getMappingObject(): array
    {
        return array(
            'id' => $this->getId(),
            'key' => $this->getKey(),
            'mappedKey' => $this->getMappedKey(),
            'position' => $this->getPosition()
        );
    }

    public function setMappingObject(string $mappingString): void
    {
        $mappingObject = json_decode($mappingString, true);
        foreach ($mappingObject as $mappingKey => $mappingValue) {

            switch ($mappingKey) {

                case 'id':
                    $this->setId($mappingValue);
                    break;
                case 'key':
                    $this->setKey($mappingValue);
                    break;
                case 'mappedKey':
                    $this->setMappedKey($mappingValue);
                    break;
                case 'position':
                    $this->setPosition($mappingValue);
                    break;
            }
        }
    }
}
