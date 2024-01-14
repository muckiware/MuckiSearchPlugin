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

namespace MuckiSearchPlugin\Search\Elasticsearch;

use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Psr\Log\LoggerInterface;

use MuckiSearchPlugin\Core\Defaults;
use MuckiSearchPlugin\Search\SearchClientInterface;
use MuckiSearchPlugin\Services\Settings as PluginSettings;
use MuckiSearchPlugin\Services\Content\IndexStructure;
use MuckiSearchPlugin\Core\Content\IndexStructure\IndexStructureTranslation\IndexStructureTranslationEntity;
use MuckiSearchPlugin\Entities\CreateIndicesBody;
use MuckiSearchPlugin\Entities\IndicesMappingProperty;
use MuckiSearchPlugin\Services\IndicesSettings;
use MuckiSearchPlugin\Services\Helper as PluginHelper;
use MuckiSearchPlugin\Entities\Mapping as MappingEntity;
use MuckiSearchPlugin\Core\Content\ServerOptions\ServerOptionsFactory;

class Client extends ClientActions implements SearchClientInterface
{
    public function __construct(
        protected PluginSettings $settings,
        protected LoggerInterface $logger,
        protected IndexStructure $indexStructure,
        protected IndicesSettings $indicesSettings,
        protected PluginHelper $pluginHelper,
        protected ServerOptionsFactory $serverOptionsFactory
    )
    {
        parent::__construct(
            $settings,
            $logger
        );
    }

    public function saveIndicesByIndexStructureId(string $indexStructureId, string $languageId, Context $context)
    {
        $indexStructure = $this->indexStructure->getIndexStructureById($indexStructureId, $languageId, $context);
        $this->indicesSettings->setTemplateVariable('entity', $indexStructure->getEntity());
        $this->indicesSettings->setTemplateVariable('salesChannelId', $indexStructure->getSalesChannelId());
        /** @var IndexStructureTranslationEntity $indexStructureTranslation */
        foreach ($indexStructure->get('translations') as $indexStructureTranslation) {

            $this->indicesSettings->setTemplateVariable('languageId', $indexStructureTranslation->getLanguageId());
            $indexName = $this->indicesSettings->getIndexNameByTemplate();
            $indexId = $this->indicesSettings->getIndexId();

            $createBody = new CreateIndicesBody($this->settings);
            $createBody->setIndexName($indexName);
            $createBody->setIndexId($indexId);
            $this->setIndicesSettings($indexStructureTranslation->get('settings'), $createBody);
            $this->setIndicesMappings($indexStructureTranslation->get('mappings'), $createBody);

            if(!$this->checkIndicesExists($indexName)) {
                $this->createNewIndices($createBody);
            } else {
                //$this->updateIndices($createBody);
            }
        }

        return null;
    }

    public function removeIndicesByIndexStructureId(string $indexStructureId, string $languageId, Context $context)
    {
        if(Uuid::isValid($indexStructureId)) {

            $indexStructure = $this->indexStructure->getIndexStructureById($indexStructureId, $languageId, $context);
            $this->indicesSettings->setTemplateVariable('salesChannelId', $indexStructure->getSalesChannelId());
            $this->indicesSettings->setTemplateVariable('entity', $indexStructure->getEntity());

            /** @var IndexStructureTranslationEntity $indexStructureTranslation */
            foreach ($indexStructure->get('translations') as $indexStructureTranslation) {

                $this->indicesSettings->setTemplateVariable('languageId', $indexStructureTranslation->getLanguageId());
                $indexName = $this->indicesSettings->getIndexNameByTemplate();

                if($this->checkIndicesExists($indexName)) {
                    $this->removeIndices($indexName);
                }
            }

            $this->indexStructure->removeIndexStructureById($indexStructureId, $context);
        }
    }

    public function removeIndicesByIndexName(string $indexName)
    {
        if($indexName !== '' && $this->checkIndicesExists($indexName)) {
            $this->removeIndices($indexName);
        }
    }

    protected function setIndicesSettings(array $settings, CreateIndicesBody $createBody): void
    {
        foreach ($settings as $setting) {

            if (array_key_exists('settingKey', $setting) && array_key_exists('settingValue', $setting)) {

                switch ($setting['settingKey']) {

                    case Defaults::INDICES_SETTINGS_NUMBER_SHARDS:
                        $createBody->setNumberOfShards($setting['settingValue']);
                        break;
                    case Defaults::INDICES_SETTINGS_NUMBER_REPLICAS:
                        $createBody->setNumberOfReplicas($setting['settingValue']);
                        break;
                }
            }
        }
    }

    protected function setIndicesMappings(array $mappings, CreateIndicesBody $createBody): void
    {
        $mappings = $this->setAdditionalMappings($mappings);
        $mappedKeys = array_column($mappings, 'key');
        $propertyPaths = array_map(fn (string $key): array => explode('.', $key), $mappedKeys);

        $createBody->setMappings(
            $this->pluginHelper->createIndicesRequestBody(
                $this->setAdditionalPropertyPaths($propertyPaths),
                $mappings
            )
        );
    }

    protected function setAdditionalPropertyPaths(array $propertyPaths): array
    {
        $additionalProductMappings = $this->serverOptionsFactory->createServerOptions()->additionalProductMappings();

        foreach ($additionalProductMappings as $additionalProductMapping) {
            $propertyPaths[][] = $additionalProductMapping['propertyPaths'];
        }

        return $propertyPaths;
    }

    /**
     * Method for to add default required mapping fields by defined MappingOptions
     *
     * @param array $mappings
     * @return array
     */
    protected function setAdditionalMappings(array $mappings): array
    {
        $additionalProductMappings = $this->serverOptionsFactory->createServerOptions()->additionalProductMappings();

        foreach ($additionalProductMappings as $additionalProductMapping) {

            $dataTypeMapping = new MappingEntity();
            $dataTypeMapping->setPosition(count($mappings));
            $dataTypeMapping->setKey($additionalProductMapping['propertyKey']);
            $dataTypeMapping->setDataType($additionalProductMapping['propertyDataType']);
            $mappings[] = $dataTypeMapping->getMapping();
        }

        return $mappings;
    }

    public function createSalesChannelProductCollection(array $resultByServer): SalesChannelProductCollection
    {
        $alesChannelProductCollection = new SalesChannelProductCollection();

        if(array_key_exists('items', $resultByServer)) {

            foreach ($resultByServer['items'] as $item) {

                $salesChannelProduct = new SalesChannelProductEntity();
                foreach ($item['source'] as $sourceKey => $sourceValue) {

                    if($sourceKey === 'id') {
                        $salesChannelProduct->{'_uniqueIdentifier'} = $sourceValue;
                    }
                    $salesChannelProduct->{$sourceKey} = $sourceValue;
                }

                $alesChannelProductCollection->add($salesChannelProduct);
            }
        }

        return $alesChannelProductCollection;
    }
}
