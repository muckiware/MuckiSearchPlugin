<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Entities;

use MuckiSearchPlugin\Services\Settings as PluginSettings;

class IndicesMappingProperty
{
    /**
     * UUID for a search mapping object
     * @var string
     */
    protected string $propertyName;

    protected string $propertyType;

    protected ?string $analyzer;

    public function __construct()
    {
        $this->analyzer = null;
    }


    /**
     * @return string
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /**
     * @param string $propertyName
     */
    public function setPropertyName(string $propertyName): void
    {
        $this->propertyName = $propertyName;
    }

    /**
     * @return string
     */
    public function getPropertyType(): string
    {
        return $this->propertyType;
    }

    /**
     * @param string $propertyType
     */
    public function setPropertyType(string $propertyType): void
    {
        $this->propertyType = $propertyType;
    }

    /**
     * @return string|null
     */
    public function getAnalyzer(): ?string
    {
        return $this->analyzer;
    }

    /**
     * @param string|null $analyzer
     */
    public function setAnalyzer(?string $analyzer): void
    {
        $this->analyzer = $analyzer;
    }

    public function getProperty(): array
    {
        $property =  array(
            $this->getPropertyName() => array(
                'type' => $this->getPropertyType()
            )
        );

        if($this->getPropertyType() === 'text' && $this->getAnalyzer()) {
            $property['analyzer'] = $this->getAnalyzer();
        }

        return $property;
    }
}
