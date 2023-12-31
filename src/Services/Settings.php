<?php 
/**
 * MuckiSearchPlugin plugin
 *
 *
 * @category   Muckiware
 * @package    MuckiSearch
 * @copyright  Copyright (c) 2023 by Muckiware
 *
 * @author     Muckiware
 *
 */

namespace MuckiSearchPlugin\Services;

use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;

use MuckiSearchPlugin\Entities\SearchMapping;
use MuckiSearchPlugin\Entities\SearchSetting;

class Settings
{
    const CONFIG_PATH_ACTIVE = 'MuckiSearchPlugin.config.active';
    const CONFIG_PATH_SERVER_TYPE = 'MuckiSearchPlugin.config.serverType';

    const CONFIG_PATH_SERVER_HOST = 'MuckiSearchPlugin.config.serverHost';
    const CONFIG_PATH_SERVER_PORT = 'MuckiSearchPlugin.config.serverPort';

    const CONFIG_PATH_MAPPING_PRODUCT_FIELDS = 'MuckiSearchPlugin.config.mappingProductFields';
    const CONFIG_PATH_DEFAULT_NUMBER_SHARDS = 'MuckiSearchPlugin.config.defaultNumberShards';
    const CONFIG_PATH_DEFAULT_NUMBER_REPLICAS = 'MuckiSearchPlugin.config.defaultNumberReplicas';

    public function __construct(
        protected SystemConfigService $config
    ){}
    
    public function isEnabled(): bool
    {
        return $this->config->getBool($this::CONFIG_PATH_ACTIVE);
    }

    public function getServerHost(): string
    {
        return $this->config->getString($this::CONFIG_PATH_SERVER_HOST);
    }

    public function getServerPort(): int
    {
        return $this->config->getInt($this::CONFIG_PATH_SERVER_PORT);
    }

    public function getServerConnectionString(): string
    {
       return $this->getServerHost().':'.$this->getServerPort();
    }

    public function getServerType(): string
    {
        return $this->config->getString($this::CONFIG_PATH_SERVER_TYPE);
    }

    public function getMappingProductFields(): array
    {
        return explode(
            ',',
            $this->config->getString($this::CONFIG_PATH_MAPPING_PRODUCT_FIELDS)
        );
    }

    public function getDefaultNumberOfShards(): int
    {
        return $this->config->getInt($this::CONFIG_PATH_DEFAULT_NUMBER_SHARDS);
    }

    public function getDefaultNumberOfReplicas(): int
    {
        return $this->config->getInt($this::CONFIG_PATH_DEFAULT_NUMBER_REPLICAS);
    }

    public function getDefaultIndicesSettings(): array
    {
        $defaultSettings = array();
        $searchMapping_1 = new SearchSetting();
        $searchMapping_1->setId(Uuid::randomHex());
        $searchMapping_1->setSettingKey('numbers_of_shards');
        $searchMapping_1->setSettingValue($this->getDefaultNumberOfShards());
        $searchMapping_1->setIsDefault(true);
        $searchMapping_1->setPosition(0);
        $defaultSettings[] = $searchMapping_1->getSettingObject();

        $searchMapping_2 = new SearchSetting();
        $searchMapping_2->setId(Uuid::randomHex());
        $searchMapping_2->setSettingKey('numbers_of_replicas');
        $searchMapping_2->setSettingValue($this->getDefaultNumberOfReplicas());
        $searchMapping_2->setIsDefault(true);
        $searchMapping_2->setPosition(1);
        $defaultSettings[] = $searchMapping_2->getSettingObject();

        return $defaultSettings;
    }

    public function getDefaultProductMapping(): array
    {
        $defaultProductMappings = array();
        $positionCounter = 0;
        foreach ($this->getMappingProductFields() as $mappingProductField) {

            $searchMapping = new SearchMapping();
            $searchMapping->setId(Uuid::randomHex());
            $searchMapping->setKey($mappingProductField);
            $searchMapping->setMappedKey($mappingProductField);
            $searchMapping->setPosition($positionCounter);
            $searchMapping->setIsDefault(true);
            $searchMapping->setdataType($mappingProductField);
            $defaultProductMappings[] = $searchMapping->getMappingObject();

            $positionCounter ++;
        }

        return $defaultProductMappings;
    }
}
