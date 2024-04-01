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

use MuckiSearchPlugin\Core\Defaults;
use MuckiSearchPlugin\Entities\SearchMapping;
use MuckiSearchPlugin\Entities\SearchSetting;

class Settings
{
    const CONFIG_PATH_ACTIVE = 'MuckiSearchPlugin.config.active';
    const CONFIG_PATH_SERVER_TYPE = 'MuckiSearchPlugin.config.serverType';

    const CONFIG_PATH_SERVER_HOST = 'MuckiSearchPlugin.config.serverHost';
    const CONFIG_PATH_SERVER_PORT = 'MuckiSearchPlugin.config.serverPort';
    const CONFIG_PATH_SERVER_AUTHENTICATION = 'MuckiSearchPlugin.config.activeAuthentication';
    const CONFIG_PATH_SERVER_AUTHENTICATION_METHOD = 'MuckiSearchPlugin.config.serverAuthenticationMethod';

    const CONFIG_PATH_SERVER_USER_NAME = 'MuckiSearchPlugin.config.serverUsername';
    const CONFIG_PATH_SERVER_USER_PASSWORD = 'MuckiSearchPlugin.config.serverPassword';

    const CONFIG_PATH_SERVER_API_KEY = 'MuckiSearchPlugin.config.serverApiKey';
    const CONFIG_PATH_SERVER_ELASTIC_CLOUD_ID = 'MuckiSearchPlugin.config.elasticCloudId';

    const CONFIG_PATH_MAPPING_PRODUCT_FIELDS = 'MuckiSearchPlugin.config.mappingProductFields';
    const CONFIG_PATH_DEFAULT_NUMBER_SHARDS = 'MuckiSearchPlugin.config.defaultNumberShards';
    const CONFIG_PATH_DEFAULT_NUMBER_REPLICAS = 'MuckiSearchPlugin.config.defaultNumberReplicas';

    const CONFIG_PATH_INDICES_SETTINGS_INDEX_NAME_PATTERN = 'MuckiSearchPlugin.config.indexNamePattern';

    const CONFIG_PATH_SEARCH_REQUEST_SETTINGS_PRE_TAGS = 'MuckiSearchPlugin.config.searchRequestSettingsPreTags';
    const CONFIG_PATH_SEARCH_REQUEST_SETTINGS_POST_TAGS = 'MuckiSearchPlugin.config.searchRequestSettingsPostTags';

    const CONFIG_PATH_SAVE_SEARCH_STATISTICS_VIA_TASK = 'MuckiSearchPlugin.config.saveSearchStatisticsViaTask';
    const CONFIG_PATH_CREATE_SEARCH_STATISTICS = 'MuckiSearchPlugin.config.createSearchStatistics';
    const CONFIG_PATH_DEFAULT_TASK_INTERVAL = 'MuckiSearchPlugin.config.defaultTaskInterval';

    public function __construct(
        protected SystemConfigService $config
    ){}
    
    public function isEnabled(): bool
    {
        return $this->config->getBool($this::CONFIG_PATH_ACTIVE);
    }

    public function getServerHost(): string
    {
        if($this->config->getString($this::CONFIG_PATH_SERVER_HOST) !== '') {
            return $this->config->getString($this::CONFIG_PATH_SERVER_HOST);
        }
        return Defaults::DEFAULT_SERVER_HOST;
    }

    public function getServerPort(): int
    {
        if($this->config->getInt($this::CONFIG_PATH_SERVER_PORT) >= 1) {
            return $this->config->getInt($this::CONFIG_PATH_SERVER_PORT);
        }
        return Defaults::DEFAULT_SERVER_PORT;
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
        $mappingProductFields = array();
        $configStr = $this->config->getString($this::CONFIG_PATH_MAPPING_PRODUCT_FIELDS);
        if($configStr !== '' && str_contains(':', $configStr)) {
            $configDefaultProductMappings = $configStr;
        } else {
            $configDefaultProductMappings = Defaults::DEFAULT_PRODUCT_MAPPINGS;
        }

        foreach (explode(',', $configDefaultProductMappings) as $mappingField) {

            $mappingFieldKeyType = explode(':', $mappingField);
            $mappingProductFields[] = array(
                'field' => $mappingFieldKeyType[0],
                'type' => $mappingFieldKeyType[1]
            );
        }

        return $mappingProductFields;
    }

    public function getDefaultNumberOfShards(): int
    {
        if($this->config->getInt($this::CONFIG_PATH_DEFAULT_NUMBER_SHARDS) >= 1) {
            return $this->config->getInt($this::CONFIG_PATH_DEFAULT_NUMBER_SHARDS);
        }
        return Defaults::DEFAULT_NUMBER_SHARDS;
    }

    public function getDefaultNumberOfReplicas(): int
    {
        if($this->config->getInt($this::CONFIG_PATH_DEFAULT_NUMBER_REPLICAS) >= 1) {
            return $this->config->getInt($this::CONFIG_PATH_DEFAULT_NUMBER_REPLICAS);
        }
        return Defaults::DEFAULT_NUMBER_REPLICAS;
    }

    public function getDefaultIndicesSettings(): array
    {
        $defaultSettings = array();
        $searchMapping_1 = new SearchSetting();
        $searchMapping_1->setId(Uuid::randomHex());
        $searchMapping_1->setSettingKey(Defaults::INDICES_SETTINGS_NUMBER_SHARDS);
        $searchMapping_1->setSettingValue($this->getDefaultNumberOfShards());
        $searchMapping_1->setIsDefault(true);
        $searchMapping_1->setPosition(0);
        $defaultSettings[] = $searchMapping_1->getSettingObject();

        $searchMapping_2 = new SearchSetting();
        $searchMapping_2->setId(Uuid::randomHex());
        $searchMapping_2->setSettingKey(Defaults::INDICES_SETTINGS_NUMBER_REPLICAS);
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
            $searchMapping->setKey($mappingProductField['field']);
            $searchMapping->setMappedKey($mappingProductField['field']);
            $searchMapping->setPosition($positionCounter);
            $searchMapping->setIsDefault(true);
            $searchMapping->setdataType($mappingProductField['type']);
            $defaultProductMappings[] = $searchMapping->getMappingObject();

            $positionCounter ++;
        }

        return $defaultProductMappings;
    }

    public function getIndexNameTemplate(): string
    {
        $indexNamePattern = $this->config->getString($this::CONFIG_PATH_INDICES_SETTINGS_INDEX_NAME_PATTERN);
        if($indexNamePattern === '') {
            return Defaults::DEFAULT_INDEX_NAME_PATTERN;
        }

        return $indexNamePattern;
    }

    public function isServerAuthenticationEnabled(): bool
    {
        return $this->config->getBool($this::CONFIG_PATH_SERVER_AUTHENTICATION);
    }

    public function getServerAuthenticationMethod(): ?string
    {
        if($this->config->getString($this::CONFIG_PATH_SERVER_AUTHENTICATION_METHOD) !== '') {
            return $this->config->getString($this::CONFIG_PATH_SERVER_AUTHENTICATION_METHOD);
        }
        return null;
    }

    public function getServerUserName(): ?string
    {
        if($this->config->getString($this::CONFIG_PATH_SERVER_USER_NAME) !== '') {
            return $this->config->getString($this::CONFIG_PATH_SERVER_USER_NAME);
        }
        return null;
    }

    public function getServerUserPassword(): ?string
    {
        if($this->config->getString($this::CONFIG_PATH_SERVER_USER_PASSWORD) !== '') {
            return $this->config->getString($this::CONFIG_PATH_SERVER_USER_PASSWORD);
        }
        return null;
    }

    public function getServerApiKey(): ?string
    {
        if($this->config->getString($this::CONFIG_PATH_SERVER_API_KEY) !== '') {
            return $this->config->getString($this::CONFIG_PATH_SERVER_API_KEY);
        }
        return null;
    }

    public function getServerElasticCloudId(): ?string
    {
        if($this->config->getString($this::CONFIG_PATH_SERVER_ELASTIC_CLOUD_ID) !== '') {
            return $this->config->getString($this::CONFIG_PATH_SERVER_ELASTIC_CLOUD_ID);
        }
        return null;
    }

    public function getSearchRequestSettingsPreTags(): ?string
    {
        if($this->config->getString($this::CONFIG_PATH_SEARCH_REQUEST_SETTINGS_PRE_TAGS) !== '') {
            return $this->config->getString($this::CONFIG_PATH_SEARCH_REQUEST_SETTINGS_PRE_TAGS);
        }
        return Defaults::SEARCH_REQUEST_SETTINGS_PRE_TAGS;
    }

    public function getSearchRequestSettingsPostTags(): ?string
    {
        if($this->config->getString($this::CONFIG_PATH_SEARCH_REQUEST_SETTINGS_POST_TAGS) !== '') {
            return $this->config->getString($this::CONFIG_PATH_SEARCH_REQUEST_SETTINGS_POST_TAGS);
        }
        return Defaults::SEARCH_REQUEST_SETTINGS_POST_TAGS;
    }

    public function isCreateSearchStatistics(): bool
    {
        return $this->config->getBool($this::CONFIG_PATH_CREATE_SEARCH_STATISTICS);
    }

    public function isSaveSearchStatisticsViaTask(): bool
    {
        return $this->config->getBool($this::CONFIG_PATH_SAVE_SEARCH_STATISTICS_VIA_TASK);
    }

    public function getDefaultTaskInterval(): int
    {
        if($this->config->getInt($this::CONFIG_PATH_DEFAULT_TASK_INTERVAL) >= 60) {
            return $this->config->getInt($this::CONFIG_PATH_DEFAULT_TASK_INTERVAL);
        }
        return Defaults::DEFAULT_TASK_INTERVAL_IN_SECONDS;
    }
}
